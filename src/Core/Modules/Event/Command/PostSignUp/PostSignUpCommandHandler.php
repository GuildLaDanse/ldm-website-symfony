<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PostSignUp;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\Event as EventEntity;
use App\Domain\Entity\Event\EventStateMachine;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Comment\CommentService;
use App\Core\Modules\Common\IntegerReference;
use App\Core\Modules\Event\DTO as EventDTO;
use App\Core\Modules\Event\EventDoesNotExistException;
use App\Core\Modules\Event\EventInThePastException;
use App\Core\Modules\Event\EventInvalidStateChangeException;
use App\Core\Modules\Event\EventService;
use App\Core\Modules\Event\UserAlreadySignedException;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostSignUpCommandHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @var EventService
     */
    private EventService $eventService;

    /**
     * @var CommentService
     */
    private CommentService $commentService;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;

    /**
     * @var AuthorizationService
     */
    private AuthorizationService $authzService;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        EventService $eventService,
        CommentService $commentService,
        AuthenticationService $authenticationService,
        AuthorizationService $authzService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->eventService = $eventService;
        $this->commentService = $commentService;
        $this->authenticationService = $authenticationService;
        $this->authzService = $authzService;
    }

    /**
     * @param PostSignUpCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PostSignUpCommand $command)
    {
        /** @var string $signupType */
        $signupType = $command->getPostSignUp()->getSignUpType();

        if (!($signupType == EventEntity\SignUpType::WILLCOME
                ||
            $signupType == EventEntity\SignUpType::MIGHTCOME
                ||
            $signupType == EventEntity\SignUpType::ABSENCE))
        {
            throw new InvalidInputException("Invalid signupType given");
        }

        if (($signupType == EventEntity\SignUpType::ABSENCE)
                &&
            ($command->getPostSignUp()->getRoles() !== null
                ||
             count($command->getPostSignUp()->getRoles()) > 0)
        )
        {
            throw new InvalidInputException("When signing as ABSENCE, roles must be empty");
        }
    }

    /**
     * @param PostSignUpCommand $command
     *
     * @return EventDTO\Event
     *
     * @throws InvalidInputException
     * @throws NotAuthorizedException
     * @throws EventDoesNotExistException
     * @throws UserAlreadySignedException
     * @throws EventInvalidStateChangeException
     * @throws EventInThePastException
     */
    public function __invoke(PostSignUpCommand $command)
    {
        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

        /* @var EntityRepository */
        $repository = $em->getRepository(EventEntity\Event::class);

        /* @var EventEntity\Event $event */
        $event = $repository->find($command->getEventId());

        if (is_null($event))
        {
            throw new EventDoesNotExistException('Event does not exist');
        }

        $oldEventDto = $this->eventService->getEventById($command->getEventId());

        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::SIGNUP_CREATE,
            new ResourceByValue(EventDTO\Event::class, $oldEventDto)
        );

        $fsm = $event->getStateMachine();

        $this->logger->info("Event has state " . $fsm->getCurrentState()->getName());
        $this->logger->info("Event state comparison " . strcmp($fsm->getCurrentState()->getName(), EventStateMachine::CONFIRMED));

        if (!(strcmp($fsm->getCurrentState()->getName(), EventStateMachine::PENDING) == 0
                ||
            strcmp($fsm->getCurrentState()->getName(), EventStateMachine::CONFIRMED) == 0))
        {
            throw new EventInvalidStateChangeException(
                'The event is not in Pending or Confirmed state, signing up is not allowed'
            );
        }

        if ($this->isUserSigned($oldEventDto, $command->getPostSignUp()->getAccountReference()))
        {
            throw new UserAlreadySignedException('User has already signed to this event');
        }

        $currentDateTime = new DateTime();
        if ($event->getInviteTime() <= $currentDateTime)
        {
            throw new EventInThePastException('Event is in the past, sign ups not allowed anymore');
        }

        $signUp = new EventEntity\SignUp();
        $signUp->setEvent($event);
        $signUp->setType($command->getPostSignUp()->getSignUpType());
        $signUp->setAccount(
            $em->getReference(
                Account::class,
                $command->getPostSignUp()->getAccountReference()->getId()
            )
        );

        foreach($command->getPostSignUp()->getRoles() as $strForRole)
        {
            $forRole = new EventEntity\ForRole();

            $forRole->setSignUp($signUp);
            $forRole->setRole($strForRole);

            $signUp->addRole($forRole);

            $em->persist($forRole);
        }

        $this->logger->info(self::class . ' persisting new sign up');

        $em->persist($signUp);
        $em->flush();

        $newEventDto = $this->eventService->getEventById($command->getEventId());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::SIGNUP_CREATE,
                $account,
                [
                    'oldEvent'   => ActivityEvent::annotatedToSimpleObject($oldEventDto),
                    'newEvent'   => ActivityEvent::annotatedToSimpleObject($newEventDto),
                    'postSignUp' => ActivityEvent::annotatedToSimpleObject($command->getPostSignUp())
                ]
            )
        );

        return $newEventDto;
    }

    private function isUserSigned(EventDTO\Event $eventDto, IntegerReference $accountReference)
    {
        foreach($eventDto->getSignUps() as $signUp)
        {
            /** @var EventDTO\SignUp $signUp */
            if ($signUp->getAccount()->getId() == $accountReference->getId())
            {
                return true;
            }
        }

        return false;
    }
}
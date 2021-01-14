<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PutSignUp;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\Event\EventStateMachine;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Domain\Entity\Event as EventEntity;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Comment\CommentService;
use App\Core\Modules\Event\DTO as EventDTO;
use App\Core\Modules\Event\EventDoesNotExistException;
use App\Core\Modules\Event\EventInThePastException;
use App\Core\Modules\Event\EventInvalidStateChangeException;
use App\Core\Modules\Event\EventService;
use App\Core\Modules\Event\SignUpDoesNotExistException;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PutSignUpCommandHandler implements MessageHandlerInterface
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
     * @param PutSignUpCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PutSignUpCommand $command)
    {
        /** @var string $signupType */
        $signupType = $command->getPutSignUp()->getSignUpType();

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
            ($command->getPutSignUp()->getRoles() !== null
                ||
             count($command->getPutSignUp()->getRoles()) > 0)
        )
        {
            throw new InvalidInputException("When signing as ABSENCE, roles must be empty");
        }
    }

    /**
     * @param PutSignUpCommand $command
     *
     * @return EventDTO\Event
     *
     * @throws SignUpDoesNotExistException
     * @throws NotAuthorizedException
     * @throws EventDoesNotExistException
     * @throws EventInvalidStateChangeException
     * @throws EventInThePastException
     */
    public function __invoke(PutSignUpCommand $command): EventDTO\Event
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

        /** @var EntityRepository $signUpRepository */
        $signUpRepository = $em->getRepository(EventEntity\SignUp::class);

        /** @var EventEntity\SignUp $signUp */
        $signUp = $signUpRepository->find($command->getSignUpId());

        if (is_null($signUp))
        {
            throw new SignUpDoesNotExistException('Sign-up does not exist');
        }

        $event = $signUp->getEvent();

        if ($event->getId() != $command->getEventId())
        {
            throw new EventDoesNotExistException('Event does not exist');
        }

        $oldEventDto = $this->eventService->getEventById($command->getEventId());

        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::SIGNUP_EDIT,
            new ResourceByValue(
                EventDTO\SignUp::class,
                $oldEventDto->getSignUpForId($command->getSignUpId())
            )
        );

        $fsm = $event->getStateMachine();

        if (!(strcmp($fsm->getCurrentState()->getName(), EventStateMachine::PENDING) == 0
                ||
            strcmp($fsm->getCurrentState()->getName(), EventStateMachine::CONFIRMED) == 0))
        {
            throw new EventInvalidStateChangeException(
                'The event is not in Pending or Confirmed state, updating a sign up is not allowed'
            );
        }

        $currentDateTime = new DateTime();
        if ($event->getInviteTime() <= $currentDateTime)
        {
            throw new EventInThePastException('Event is in the past, updating sign-up is not allowed anymore');
        }

        $signUp->setType($command->getPutSignUp()->getSignUpType());

        foreach($signUp->getRoles() as $origRole)
        {
            $em->remove($origRole);
        }

        $signUp->getRoles()->clear();

        if ($command->getPutSignUp()->getSignUpType() != EventEntity\SignUpType::ABSENCE)
        {
            foreach($command->getPutSignUp()->getRoles() as $strForRole)
            {
                $forRole = new EventEntity\ForRole();

                $forRole->setSignUp($signUp);
                $forRole->setRole($strForRole);

                $signUp->addRole($forRole);

                $em->persist($forRole);
            }
        }

        $this->logger->info(__CLASS__ . ' persisting new sign up');

        $em->persist($signUp);
        $em->flush();

        $newEventDto = $this->eventService->getEventById($command->getEventId());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::SIGNUP_EDIT,
                $account,
                [
                    'oldEvent'  => ActivityEvent::annotatedToSimpleObject($oldEventDto),
                    'newEvent'  => ActivityEvent::annotatedToSimpleObject($newEventDto),
                    'signUpId'  => $command->getSignUpId(),
                    'putSignUp' => ActivityEvent::annotatedToSimpleObject($command->getPutSignUp())
                ]
            )
        );

        return $newEventDto;
    }
}
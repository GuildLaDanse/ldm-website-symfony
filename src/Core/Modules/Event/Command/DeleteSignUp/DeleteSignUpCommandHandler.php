<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\DeleteSignUp;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Domain\Entity\Event as EventEntity;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Event\DTO as EventDTO;
use App\Core\Modules\Event\EventInThePastException;
use App\Core\Modules\Event\EventInvalidStateChangeException;
use App\Core\Modules\Event\EventService;
use App\Core\Modules\Event\SignUpDoesNotExistException;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DeleteSignUpCommandHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    public LoggerInterface $logger;

    /**
     * @var EventDispatcherInterface
     */
    public EventDispatcherInterface $eventDispatcher;

    /**
     * @var ManagerRegistry
     */
    public ManagerRegistry $doctrine;

    /**
     * @var AuthenticationService
     */
    public AuthenticationService $authenticationService;

    /**
     * @var AuthorizationService
     */
    public AuthorizationService $authzService;

    /**
     * @var EventService
     */
    public EventService $eventService;

    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        AuthenticationService $authenticationService,
        AuthorizationService $authzService,
        EventService $eventService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->authenticationService = $authenticationService;
        $this->authzService = $authzService;
        $this->eventService = $eventService;
    }

    /**
     * @param DeleteSignUpCommand $command
     *
     * @return EventDTO\Event
     *
     * @throws SignUpDoesNotExistException
     * @throws NotAuthorizedException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EventInThePastException
     * @throws EventInvalidStateChangeException
     */
    public function __invoke(DeleteSignUpCommand $command): EventDTO\Event
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /* @var EntityRepository $repository */
        $repository = $this->doctrine->getRepository(EventEntity\SignUp::class);

        /* @var EventEntity\SignUp $signUp */
        $signUp = $repository->find($command->getSignUpId());

        if ($signUp === null)
        {
            throw new SignUpDoesNotExistException("Sign up with id " . $command->getSignUpId() . ' does not exist');
        }

        $oldEventDto = $this->eventService->getEventById($command->getEventId());

        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::SIGNUP_DELETE,
            new ResourceByValue(
                EventDTO\SignUp::class,
                $oldEventDto->getSignUpForId($command->getSignUpId())
            )
        );

        $currentDateTime = new DateTime();
        if ($signUp->getEvent()->getInviteTime() <= $currentDateTime)
        {
            throw new EventInThePastException("Event belonging to sign up is in the past and cannot be changed");
        }

        if (!($oldEventDto->getState() == 'Pending' || $oldEventDto->getState() == 'Confirmed'))
        {
            throw new EventInvalidStateChangeException(
                'The event is not in Pending or Confirmed state, sign-up removals are not allowed'
            );
        }

        $em->remove($signUp);
        $em->flush();

        $newEventDto = $this->eventService->getEventById($command->getEventId());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::SIGNUP_DELETE,
                $account,
                [
                    'oldEvent' => ActivityEvent::annotatedToSimpleObject($oldEventDto),
                    'newEvent' => ActivityEvent::annotatedToSimpleObject($newEventDto),
                    'signUpId' => $command->getSignUpId()
                ]
            )
        );

        return $newEventDto;
    }
}
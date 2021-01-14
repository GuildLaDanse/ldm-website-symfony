<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PutEventState;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\Event\EventStateMachine;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Comment\CommentService;
use App\Core\Modules\Event\EventDoesNotExistException;
use App\Core\Modules\Event\EventInvalidStateChangeException;
use App\Core\Modules\Event\EventService;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Event as EventEntity;
use App\Core\Modules\Event\DTO as EventDTO;

class PutEventStateCommandHandler implements MessageHandlerInterface
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
     * @param PutEventStateCommand $command
     *
     * @return mixed
     *
     * @throws EventDoesNotExistException
     * @throws NotAuthorizedException
     * @throws EventInvalidStateChangeException
     */
    public function __invoke(PutEventStateCommand $command)
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

        $oldEventDto = $this->eventService->getEventById($command->getEventId());

        if ($oldEventDto === null)
        {
            throw new EventDoesNotExistException("Event does not exist " . $command->getEventId());
        }

        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::EVENT_PUT_STATE,
            new ResourceByValue(EventDTO\Event::class, $oldEventDto)
        );

        /* @var $repository EntityRepository */
        $repository = $this->doctrine->getRepository(EventEntity\Event::class);

        /* @var EventEntity\Event $event */
        $event = $repository->find($command->getEventId());

        $desiredStateTransition = $this->getStateTransition($command->getPutEventState()->getState());

        if (($desiredStateTransition !== null) && $event->getStateMachine()->can($desiredStateTransition))
        {
            $event->getStateMachine()->apply($desiredStateTransition);
        }
        else
        {
            throw new EventInvalidStateChangeException('The event does not allow a transition to the requested state');
        }

        $this->logger->info(__CLASS__ . ' changing state of event');

        $em->flush();

        $eventDto = $this->eventService->getEventById($event->getId());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::EVENT_PUT_STATE,
                $account,
                [
                    'oldEvent'      => ActivityEvent::annotatedToSimpleObject($oldEventDto),
                    'newEvent'      => ActivityEvent::annotatedToSimpleObject($eventDto),
                    'putEventState' => ActivityEvent::annotatedToSimpleObject($command->getPutEventState())
                ]
            )
        );

        return $eventDto;
    }

    /**
     * @param string $desiredState
     *
     * @return string|null
     */
    private function getStateTransition(string $desiredState): ?string
    {
        if ($desiredState == EventStateMachine::CONFIRMED)
            return EventStateMachine::TR_CONFIRM;

        if ($desiredState == EventStateMachine::CANCELLED)
            return EventStateMachine::TR_CANCEL;

        if ($desiredState == EventStateMachine::HAPPENED)
            return EventStateMachine::TR_CONFIRM_HAPPENED;

        if ($desiredState == EventStateMachine::NOTHAPPENED)
            return EventStateMachine::TR_CONFIRM_NOT_HAPPENED;

        if ($desiredState == EventStateMachine::ARCHIVED)
            return EventStateMachine::TR_CONFIRM_NOT_HAPPENED;

        if ($desiredState == EventStateMachine::ARCHIVED)
            return EventStateMachine::TR_ARCHIVE;

        if ($desiredState == EventStateMachine::DELETED)
            return EventStateMachine::TR_DELETE;

        return null;
    }
}
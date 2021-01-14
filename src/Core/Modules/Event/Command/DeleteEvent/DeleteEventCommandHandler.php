<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\DeleteEvent;

use App\Domain\Entity\Event as EventEntity;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Comment\CommentGroupDoesNotExistException;
use App\Core\Modules\Comment\CommentService;
use App\Core\Modules\Event\DTO as EventDTO;
use App\Core\Modules\Event\EventDoesNotExistException;
use App\Core\Modules\Event\EventInThePastException;
use App\Core\Modules\Event\EventService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DeleteEventCommandHandler implements MessageHandlerInterface
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
     * @var EventService
     */
    public EventService $eventService;

    /**
     * @var CommentService
     */
    public CommentService $commentService;

    /**
     * @var AuthenticationService
     */
    public AuthenticationService $authenticationService;

    /**
     * @var AuthorizationService
     */
    public AuthorizationService $authzService;

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
     * @param DeleteEventCommand $command
     *
     * @throws EventDoesNotExistException
     * @throws NotAuthorizedException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws EventInThePastException
     * @throws CommentGroupDoesNotExistException
     */
    public function __invoke(DeleteEventCommand $command)
    {
        $eventDto = $this->eventService->getEventById($command->getEventId());

        if ($eventDto === null)
        {
            throw new EventDoesNotExistException("Event does not exist, id = " . $command->getEventId());
        }

        $this->authzService->allowOrThrow(
            new SubjectReference($this->authenticationService->getCurrentContext()->getAccount()),
            ActivityType::EVENT_DELETE,
            new ResourceByValue(EventDTO\Event::class, $eventDto)
        );

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /* @var EntityRepository $repository */
        $repository = $this->doctrine->getRepository(EventEntity\Event::class);

        /* @var EventEntity\Event $event */
        $event = $repository->find($command->getEventId());

        $currentDateTime = new DateTime();
        if ($event->getInviteTime() <= $currentDateTime)
        {
            throw new EventInThePastException("Event is in the past and cannot be changed");
        }

        $this->commentService->removeCommentGroup($event->getTopicId());

        $em->remove($event);

        $em->flush();

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::EVENT_DELETE,
                $this->authenticationService->getCurrentContext()->getAccount(),
                [
                    'event' => ActivityEvent::annotatedToSimpleObject($eventDto)
                ]
            )
        );
    }
}
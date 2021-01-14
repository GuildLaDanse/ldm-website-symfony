<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PostEvent;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\Event as EventEntity;
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
use App\Core\Modules\Event\DTO as EventDTO;
use App\Core\Modules\Event\EventService;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostEventCommandHandler implements MessageHandlerInterface
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
     * @param PostEventCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PostEventCommand $command)
    {
        $inviteTime = $command->getPostEventDto()->getInviteTime();
        $startTime = $command->getPostEventDto()->getStartTime();
        $endTime = $command->getPostEventDto()->getEndTime();

        if (!(($inviteTime <= $startTime) && ($startTime <= $endTime)))
        {
            throw new InvalidInputException("Violation of time constraints: inviteTime <= startTime <= endTime");
        }
    }

    /**
     * @param PostEventCommand $command
     *
     * @return EventDTO\Event
     *
     * @throws InvalidInputException
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function __invoke(PostEventCommand $command): EventDTO\Event
    {
        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::EVENT_CREATE,
            new ResourceByValue(EventDTO\PostEvent::class, $command->getPostEventDto())
        );

        $em = $this->doctrine->getManager();

        $commentGroupId = $this->commentService->createCommentGroup();

        $event = new EventEntity\Event();
        $event->setOrganiser(
            $em->getReference(
                Account::class,
                $command->getPostEventDto()->getOrganiserReference()->getId()
            )
        );
        $event->setName($command->getPostEventDto()->getName());
        $event->setDescription($command->getPostEventDto()->getDescription());
        $event->setInviteTime($command->getPostEventDto()->getInviteTime());
        $event->setStartTime($command->getPostEventDto()->getStartTime());
        $event->setEndTime($command->getPostEventDto()->getEndTime());
        $event->setTopicId($commentGroupId);

        $this->logger->info(__CLASS__ . ' persisting event');

        $em->persist($event);
        $em->flush();

        $eventDto = $this->eventService->getEventById($event->getId());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::EVENT_CREATE,
                $account,
                [
                    'event' => ActivityEvent::annotatedToSimpleObject($eventDto)
                ]
            )
        );

        return $eventDto;
    }
}
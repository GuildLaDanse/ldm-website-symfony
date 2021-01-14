<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PutEvent;

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
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PutEventCommandHandler implements MessageHandlerInterface
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
     * @param PutEventCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PutEventCommand $command)
    {
        $inviteTime = $command->getPutEventDto()->getInviteTime();
        $startTime = $command->getPutEventDto()->getStartTime();
        $endTime = $command->getPutEventDto()->getEndTime();

        if (!(($inviteTime <= $startTime) && ($startTime <= $endTime)))
        {
            throw new InvalidInputException("Violation of time constraints: inviteTime <= startTime <= endTime");
        }
    }

    /**
     * @param PutEventCommand $command
     *
     * @return mixed
     *
     * @throws InvalidInputException
     * @throws NotAuthorizedException
     */
    public function __invoke(PutEventCommand $command)
    {
        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

        /** @var EventEntity\Event $event */
        $event = $em->getRepository(EventEntity\Event::class)->find($command->getEventId());

        $oldEventDto = $this->eventService->getEventById($event->getId());

        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::EVENT_EDIT,
            new ResourceByValue(EventDTO\Event::class, $oldEventDto)
        );

        $event->setOrganiser(
            $em->getReference(
                Account::class,
                $command->getPutEventDto()->getOrganiserReference()->getId()
            )
        );
        $event->setName($command->getPutEventDto()->getName());
        $event->setDescription($command->getPutEventDto()->getDescription());
        $event->setInviteTime($command->getPutEventDto()->getInviteTime());
        $event->setStartTime($command->getPutEventDto()->getStartTime());
        $event->setEndTime($command->getPutEventDto()->getEndTime());

        $this->logger->info(__CLASS__ . ' updating event');

        $em->flush();

        $newEventDto = $this->eventService->getEventById($event->getId());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::EVENT_EDIT,
                $account,
                [
                    'oldEvent' => ActivityEvent::annotatedToSimpleObject($oldEventDto),
                    'newEvent' => ActivityEvent::annotatedToSimpleObject($newEventDto),
                    'putEvent' => ActivityEvent::annotatedToSimpleObject($command->getPutEventDto()),
                ]
            )
        );

        return $newEventDto;
    }
}
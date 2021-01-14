<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Query\GetEventById;

use App\Domain\Entity\Event as EntityEvent;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Event\DTO as EventDTO;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\Event\DTO\EventMapper;
use App\Core\Modules\Event\EventDoesNotExistException;
use App\Core\Modules\Event\Query\EventHydrator;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class GetEventByIdQueryHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @var EventHydrator
     */
    private EventHydrator $eventHydrator;

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
        ManagerRegistry $doctrine,
        EventHydrator $eventHydrator,
        AuthenticationService $authenticationService,
        AuthorizationService $authzService)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
        $this->eventHydrator = $eventHydrator;
        $this->authenticationService = $authenticationService;
        $this->authzService = $authzService;
    }

    /**
     * @param GetEventByIdQuery $query
     *
     * @return EventDTO\Event
     *
     * @throws EventDoesNotExistException
     * @throws NotAuthorizedException
     * @throws MapperException
     */
    public function __invoke(GetEventByIdQuery $query): EventDTO\Event
    {
        /** @var EntityRepository $repository */
        $repository = $this->doctrine->getRepository(EntityEvent\Event::class);

        /** @var EntityEvent\Event $event */
        $event = $repository->find($query->getEventId());

        if ($event === null)
        {
            throw new EventDoesNotExistException('Event does not exist');
        }

        $this->authzService->allowOrThrow(
            new SubjectReference($this->authenticationService->getCurrentContext()->getAccount()),
            ActivityType::EVENT_VIEW,
            new ResourceByValue(EntityEvent\Event::class, $event)
        );

        $eventIds = [$event->getId()];

        $this->eventHydrator->setEventIds($eventIds);

        return EventMapper::mapSingle($event, $this->eventHydrator);
    }
}
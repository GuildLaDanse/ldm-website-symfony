<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Query\GetAllEventsPaged;

use App\Domain\Entity\Event as EntityEvent;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\NotAuthorizedException;
use App\Infrastructure\Authorization\NullResourceReference;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\Event\Query\EventHydrator;
use App\Core\Modules\Event\DTO as EventDTO;
use DateInterval;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class GetAllEventsPagedQueryHandler implements MessageHandlerInterface
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
    public AuthorizationService $authzService;
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        EventHydrator $eventHydrator,
        AuthenticationService $authenticationService,
        AuthorizationService $authzService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->eventHydrator = $eventHydrator;
        $this->authenticationService = $authenticationService;
        $this->authzService = $authzService;
    }

    /**
     * @param GetAllEventsPagedQuery $query
     *
     * @return EventDTO\EventPage
     *
     * @throws InvalidInputException
     * @throws NotAuthorizedException
     * @throws MapperException
     */
    public function __invoke(GetAllEventsPagedQuery $query): EventDTO\EventPage
    {
        if ($query->getStartOn() === null)
        {
            throw new InvalidInputException('A valid date is required for startOn');
        }

        $this->authzService->allowOrThrow(
            new SubjectReference($this->authenticationService->getCurrentContext()->getAccount()),
            ActivityType::EVENT_LIST,
            new NullResourceReference()
        );

        $query->getStartOn()->setTime(0, 0, 0);

        $beforeDate = clone $query->getStartOn();
        $beforeDate->add(new DateInterval('P28D'));
        $beforeDate->setTime(23, 59, 59);

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('event')
            ->from(EntityEvent\Event::class, 'event')
            ->where($qb->expr()->andX(
                $qb->expr()->gte('event.inviteTime', ':startOn'),
                $qb->expr()->lt('event.inviteTime', ':beforeDate')
            ))
            ->orderBy('event.inviteTime', 'ASC')
            ->setParameter('startOn', $query->getStartOn())
            ->setParameter('beforeDate', $beforeDate);

        $this->logger->debug(
            self::class . ' created DQL for retrieving Events ',
            [
                'query' => $qb->getDQL()
            ]
        );

        /* @var Query */
        $dbQuery = $qb->getQuery();

        $events = $dbQuery->getResult();

        $eventIds = [];

        foreach($events as $event)
        {
            /** @var EntityEvent\Event $event */
            $eventIds[] = $event->getId();
        }

        $this->eventHydrator->setEventIds($eventIds);

        $previousTimestamp = clone $query->getStartOn();
        $previousTimestamp->sub(new DateInterval('P28D'));

        $nextTimestamp = clone $query->getStartOn();
        $nextTimestamp->add(new DateInterval('P28D'));

        $eventPage = new EventDTO\EventPage();
        $eventPage
            ->setEvents(EventDTO\EventMapper::mapArray($events, $this->eventHydrator))
            ->setPreviousTimestamp($previousTimestamp)
            ->setNextTimestamp($nextTimestamp);

        return $eventPage;
    }
}
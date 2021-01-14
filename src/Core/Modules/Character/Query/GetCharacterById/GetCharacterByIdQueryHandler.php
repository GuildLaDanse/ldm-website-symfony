<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\GetCharacterById;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Character\Query\CharacterHydrator;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;
use App\Core\Modules\Character\DTO as CharacterDTO;

class GetCharacterByIdQueryHandler implements MessageHandlerInterface
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
     * @var CharacterHydrator
     */
    private CharacterHydrator $characterHydrator;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;

    /**
     * GetAllCharactersInGuildQueryHandler constructor.
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $doctrine
     * @param CharacterHydrator $characterHydrator
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        CharacterHydrator $characterHydrator,
        AuthenticationService $authenticationService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->characterHydrator = $characterHydrator;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param GetCharacterByIdQuery $query
     *
     * @return CharacterDTO\Character
     *
     * @throws ServiceException
     */
    public function __invoke(GetCharacterByIdQuery $query): CharacterDTO\Character
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('characterVersion', 'character', 'realm', 'gameClass', 'gameRace')
            ->from(CharacterEntity\CharacterVersion::class, 'characterVersion')
            ->join('characterVersion.character', 'character')
            ->join('characterVersion.gameClass', 'gameClass')
            ->join('characterVersion.gameRace', 'gameRace')
            ->join('character.realm', 'realm')
            ->add('where',
                $qb->expr()->andX(
                    $qb->expr()->eq('character.id', '?1'),
                    $qb->expr()->orX(
                        $qb->expr()->andX(
                            $qb->expr()->lte('characterVersion.fromTime', '?2'),
                            $qb->expr()->gt('characterVersion.endTime', '?2')
                        ),
                        $qb->expr()->andX(
                            $qb->expr()->lte('characterVersion.fromTime', '?2'),
                            $qb->expr()->isNull('characterVersion.endTime')
                        )
                    )
                )
            )
            ->setParameter(1, $query->getCharacterId())
            ->setParameter(2, $query->getOnDateTime());

        /* @var Query $dbQuery */
        $dbQuery = $qb->getQuery();

        $characterVersions = $dbQuery->getResult();

        if (count($characterVersions) != 1)
        {
            throw new ServiceException(
                sprintf(
                    "A character with id %s was not found",
                    $query->getCharacterId()
                ),
                500
            );
        }

        /** @var CharacterEntity\CharacterVersion $characterVersion */
        $characterVersion = $characterVersions[0];

        $this->characterHydrator->setCharacterIds([$query->getCharacterId()]);
        $this->characterHydrator->setOnDateTime($query->getOnDateTime());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::QUERY_GET_CHARACTER_BY_ID,
                $account,
                [
                    'accountId'   => $this->authenticationService->getCurrentContext()->isAuthenticated() ? $account : null,
                    'characterId' => $query->getCharacterId(),
                    'onDateTime'  => $query->getOnDateTime()
                ]
            )
        );

        return CharacterDTO\CharacterMapper::mapSingle($characterVersion, $this->characterHydrator);
    }
}
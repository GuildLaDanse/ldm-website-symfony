<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\GetAllCharactersInGuild;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Character\Query\CharacterHydrator;
use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\GameData as GameDataEntity;
use App\Domain\Entity\Character as CharacterEntity;
use App\Core\Modules\Character\DTO as CharacterDTO;

class GetAllCharactersInGuildQueryHandler implements MessageHandlerInterface
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
     * @param GetAllCharactersInGuildQuery $query
     * @throws InvalidInputException
     */
    private function validateInput(GetAllCharactersInGuildQuery $query)
    {
        if ($query->getOnDateTime() === null
            || $query->getGuildReference() === null
            || $query->getGuildReference()->getId() === null)
        {
            throw new InvalidInputException("Input for " . __CLASS__ . " is not valid");
        }
    }

    /**
     * @param GetAllCharactersInGuildQuery $query
     *
     * @return array
     *
     * @throws Exception
     */
    public function __invoke(GetAllCharactersInGuildQuery $query)
    {
        $this->validateInput($query);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('g', 'realm')
            ->from(GameDataEntity\Guild::class, 'g')
            ->join('g.realm', 'realm')
            ->where('g.id = ?1')
            ->setParameter(1, $query->getGuildReference()->getId());

        $this->logger->debug(
            __CLASS__ . " created DQL for retrieving GameRaces ",
            [
                "query" => $qb->getDQL()
            ]
        );

        /* @var Query $dbQuery
         */
        $dbQuery = $qb->getQuery();

        $guilds = $dbQuery->getResult();

        if (count($guilds) != 1)
        {
            // throw exception
            return null;
        }

        /** @var GameDataEntity\Guild $guild */
        $guild = $guilds[0];

        $onDateTime = $query->getOnDateTime() ?? new DateTime();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        /** @var QueryBuilder $innerQb */
        $innerQb = $em->createQueryBuilder();

        $qb->select('characterVersion', 'character', 'realm', 'gameClass', 'gameRace')
            ->from(CharacterEntity\CharacterVersion::class, 'characterVersion')
            ->join('characterVersion.character', 'character')
            ->join('characterVersion.gameClass', 'gameClass')
            ->join('characterVersion.gameRace', 'gameRace')
            ->join('character.realm', 'realm')
            ->add('where',
                $qb->expr()->andX(
                    $qb->expr()->in(
                        'characterVersion.character',
                        $innerQb->select('innerCharacter.id')
                            ->from(CharacterEntity\InGuild::class, 'inGuild')
                            ->join('inGuild.character', 'innerCharacter')
                            ->add('where',
                                $qb->expr()->andX(
                                    $qb->expr()->eq('inGuild.guild', '?1'),
                                    $qb->expr()->orX(
                                        $qb->expr()->andX(
                                            $qb->expr()->lte('inGuild.fromTime', '?2'),
                                            $qb->expr()->gt('inGuild.endTime', '?2')
                                        ),
                                        $qb->expr()->andX(
                                            $qb->expr()->lte('inGuild.fromTime', '?2'),
                                            $qb->expr()->isNull('inGuild.endTime')
                                        )
                                    )
                                )
                            )->getDQL()
                    ),
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
            ->setParameter(1, $guild)
            ->setParameter(2, $onDateTime);

        /* @var Query $dbQuery */
        $dbQuery = $qb->getQuery();

        $characterVersions = $dbQuery->getResult();

        $characterIds = [];

        foreach($characterVersions as $characterVersion)
        {
            /** @var CharacterEntity\CharacterVersion $characterVersion */

            $characterIds[] = $characterVersion->getCharacter()->getId();
        }

        $this->characterHydrator->setCharacterIds($characterIds);
        $this->characterHydrator->setOnDateTime($onDateTime);

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::QUERY_GET_ALL_CHARACTERS_IN_GUILD,
                $account,
                [
                    'accountId'      => $this->authenticationService->getCurrentContext()->isAuthenticated() ? $account : null,
                    'guildReference' => $query->getGuildReference(),
                    'onDateTime'     => $onDateTime
                ]
            )
        );

        return CharacterDTO\CharacterMapper::mapArray($characterVersions, $this->characterHydrator);
    }
}
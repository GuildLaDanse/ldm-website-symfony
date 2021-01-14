<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\CharactersByCriteria;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\Character as CharacterEntity;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Character\DTO as CharacterDTO;
use App\Core\Modules\Character\Query\CharacterHydrator;
use App\Core\Modules\Common\MapperException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CharactersByCriteriaQueryHandler implements MessageHandlerInterface
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
     * CharactersByCriteriaQueryHandler constructor.
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
     * @param CharactersByCriteriaQuery $query
     *
     * @throws InvalidInputException
     */
    protected function validateInput(CharactersByCriteriaQuery $query)
    {
        if ($query->getOnDateTime() === null)
        {
            throw new InvalidInputException("Input for " . __CLASS__ . " is not valid");
        }
    }

    /**
     * @param CharactersByCriteriaQuery $query
     *
     * @return array
     *
     * @throws InvalidInputException
     * @throws MapperException
     */
    public function __invoke(CharactersByCriteriaQuery $query)
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $this->validateInput($query);

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $whereClause = $qb->expr()->andX(
            $qb->expr()->andX(
                $qb->expr()->gte('characterVersion.level', ':minLevel'),
                $qb->expr()->lte('characterVersion.level', ':maxLevel')
            ),
            $qb->expr()->andX(
                $qb->expr()->like('character.name', ':name'),
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->lte('characterVersion.fromTime', ':onDateTime'),
                        $qb->expr()->gt('characterVersion.endTime', ':onDateTime')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->lte('characterVersion.fromTime', ':onDateTime'),
                        $qb->expr()->isNull('characterVersion.endTime')
                    )
                )
            )
        );

        $guildParamRequired = false;
        $raceParamRequired = false;
        $classParamRequired = false;
        $factionParamRequired = false;
        $rolesParamRequired = false;
        $claimingMemberParamRequired = false;

        // if Race is selected, add clause
        if ($query->getSearchCriteria()->getGameRace() !== null)
        {
            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->eq('gameRace.id', ':gameRaceId')
            );
            $raceParamRequired = true;
        }

        // if Class is selected, add clause
        if ($query->getSearchCriteria()->getGameClass() !== null)
        {
            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->eq('gameClass.id', ':gameClassId')
            );
            $classParamRequired = true;
        }

        // if Faction is selected, add clause
        if ($query->getSearchCriteria()->getGameFaction() !== null)
        {
            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->eq('gameFaction.id', ':gameFactionId')
            );
            $factionParamRequired = true;
        }

        // if Guild is selected, add clause (this is a sub-query)
        if ($query->getSearchCriteria()->getGuild() !== null)
        {
            /** @var QueryBuilder $innerGuildQb */
            $innerGuildQb = $em->createQueryBuilder();

            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->in(
                    'characterVersion.character',
                    $innerGuildQb->select('innerCharacter.id')
                        ->from(CharacterEntity\InGuild::class, 'inGuild')
                        ->join('inGuild.character', 'innerCharacter')
                        ->join('inGuild.guild', 'innerGuild')
                        ->add('where',
                            $qb->expr()->andX(
                                $qb->expr()->eq('innerGuild.id', ':guildId'),
                                $qb->expr()->orX(
                                    $qb->expr()->andX(
                                        $qb->expr()->lte('inGuild.fromTime', ':onDateTime'),
                                        $qb->expr()->gt('inGuild.endTime', ':onDateTime')
                                    ),
                                    $qb->expr()->andX(
                                        $qb->expr()->lte('inGuild.fromTime', ':onDateTime'),
                                        $qb->expr()->isNull('inGuild.endTime')
                                    )
                                )
                            )
                        )->getDQL()
                )
            );
            $guildParamRequired = true;
        }

        // if any of "only raider" or "only non-raider" is selected, add clause (this is a sub-query)
        if (($query->getSearchCriteria()->getRaider() == 2) || ($query->getSearchCriteria()->getRaider() == 3))
        {
            $raiderValue = $query->getSearchCriteria()->getRaider() == 2 ? 1 : 0;

            /** @var QueryBuilder $innerRaiderdQb */
            $innerRaiderdQb = $em->createQueryBuilder();

            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->in(
                    'characterVersion.character',
                    $innerRaiderdQb->select('innerRaiderCharacter.id')
                        ->from(CharacterEntity\ClaimVersion::class, 'raiderClaimVersion')
                        ->join('raiderClaimVersion.claim', 'raiderClaim')
                        ->join('raiderClaim.character', 'innerRaiderCharacter')
                        ->add('where',
                            $qb->expr()->andX(
                                $qb->expr()->eq('raiderClaimVersion.raider', $raiderValue),
                                $qb->expr()->orX(
                                    $qb->expr()->andX(
                                        $qb->expr()->lte('raiderClaimVersion.fromTime', ':onDateTime'),
                                        $qb->expr()->gt('raiderClaimVersion.endTime', ':onDateTime')
                                    ),
                                    $qb->expr()->andX(
                                        $qb->expr()->lte('raiderClaimVersion.fromTime', ':onDateTime'),
                                        $qb->expr()->isNull('raiderClaimVersion.endTime')
                                    )
                                )
                            )
                        )->getDQL()
                )
            );
        }

        // if "only claimed" is selected, add clause (this is a sub-query)
        if (($query->getSearchCriteria()->getClaimed() == 2) && ($query->getSearchCriteria()->getRaider() == 1))
        {
            /** @var QueryBuilder $innerRaiderdQb */
            $innerRaiderdQb = $em->createQueryBuilder();

            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->in(
                    'characterVersion.character',
                    $innerRaiderdQb->select('innerClaimCharacter.id')
                        ->from(CharacterEntity\Claim::class, 'claim')
                        ->join('claim.character', 'innerClaimCharacter')
                        ->add('where',
                            $qb->expr()->orX(
                                $qb->expr()->andX(
                                    $qb->expr()->lte('claim.fromTime', ':onDateTime'),
                                    $qb->expr()->gt('claim.endTime', ':onDateTime')
                                ),
                                $qb->expr()->andX(
                                    $qb->expr()->lte('claim.fromTime', ':onDateTime'),
                                    $qb->expr()->isNull('claim.endTime')
                                )
                            )
                        )->getDQL()
                )
            );
        }

        // if "only non-claimed" is selected, add clause (this is a sub-query)
        if (($query->getSearchCriteria()->getClaimed() == 3) && ($query->getSearchCriteria()->getRaider() == 1))
        {
            /** @var QueryBuilder $innerRaiderdQb */
            $innerRaiderdQb = $em->createQueryBuilder();

            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->notIn(
                    'characterVersion.character',
                    $innerRaiderdQb->select('innerClaimCharacter.id')
                        ->from(CharacterEntity\Claim::class, 'claim')
                        ->join('claim.character', 'innerClaimCharacter')
                        ->add('where',
                            $qb->expr()->orX(
                                $qb->expr()->andX(
                                    $qb->expr()->lte('claim.fromTime', ':onDateTime'),
                                    $qb->expr()->gt('claim.endTime', ':onDateTime')
                                ),
                                $qb->expr()->andX(
                                    $qb->expr()->lte('claim.fromTime', ':onDateTime'),
                                    $qb->expr()->isNull('claim.endTime')
                                )
                            )
                        )->getDQL()
                )
            );
        }

        // if "only non-claimed" is selected, add clause (this is a sub-query)
        if ($query->getSearchCriteria()->getClaimingMember() !== null)
        {
            /** @var QueryBuilder $innerClaimingMemberQb */
            $innerClaimingMemberQb = $em->createQueryBuilder();

            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->in(
                    'characterVersion.character',
                    $innerClaimingMemberQb->select('innerClaimingMemberCharacter.id')
                        ->from(CharacterEntity\Claim::class, 'claimingMemberClaim')
                        ->join('claimingMemberClaim.character', 'innerClaimingMemberCharacter')
                        ->join('claimingMemberClaim.account', 'innerClaimingMemberAccount')
                        ->add('where',
                            $innerClaimingMemberQb->expr()->andX(
                                $innerClaimingMemberQb->expr()->like('innerClaimingMemberAccount.displayName', ':claimingMember'),
                                $innerClaimingMemberQb->expr()->orX(
                                    $innerClaimingMemberQb->expr()->andX(
                                        $innerClaimingMemberQb->expr()->lte('claimingMemberClaim.fromTime', ':onDateTime'),
                                        $innerClaimingMemberQb->expr()->gt('claimingMemberClaim.endTime', ':onDateTime')
                                    ),
                                    $innerClaimingMemberQb->expr()->andX(
                                        $innerClaimingMemberQb->expr()->lte('claimingMemberClaim.fromTime', ':onDateTime'),
                                        $innerClaimingMemberQb->expr()->isNull('claimingMemberClaim.endTime')
                                    )
                                )
                            )
                        )->getDQL()
                )
            );

            $claimingMemberParamRequired = true;
        }

        // if "only non-claimed" is selected, add clause (this is a sub-query)
        if (($query->getSearchCriteria()->getRoles() !== null) && (count($query->getSearchCriteria()->getRoles()) > 0))
        {
            /** @var QueryBuilder $innerRolesQb */
            $innerRolesQb = $em->createQueryBuilder();

            $whereClause = $qb->expr()->andX(
                $whereClause,
                $qb->expr()->in(
                    'characterVersion.character',
                    $innerRolesQb->select('innerRolesCharacter.id')
                        ->from(CharacterEntity\PlaysRole::class, 'playsRole')
                        ->join('playsRole.claim', 'innerRolesClaim')
                        ->join('innerRolesClaim.character', 'innerRolesCharacter')
                        ->add('where',
                            $innerRolesQb->expr()->andX(
                                $innerRolesQb->expr()->in('playsRole.role', ':roles'),
                                $innerRolesQb->expr()->orX(
                                    $innerRolesQb->expr()->andX(
                                        $innerRolesQb->expr()->lte('playsRole.fromTime', ':onDateTime'),
                                        $innerRolesQb->expr()->gt('playsRole.endTime', ':onDateTime')
                                    ),
                                    $innerRolesQb->expr()->andX(
                                        $innerRolesQb->expr()->lte('playsRole.fromTime', ':onDateTime'),
                                        $innerRolesQb->expr()->isNull('playsRole.endTime')
                                    )
                                )
                            )
                        )->getDQL()
                )
            );

            $rolesParamRequired = true;
        }

        $qb->select('characterVersion', 'character', 'realm', 'gameClass', 'gameRace')
           ->from(CharacterEntity\CharacterVersion::class, 'characterVersion')
           ->join('characterVersion.character', 'character')
           ->join('characterVersion.gameClass', 'gameClass')
           ->join('characterVersion.gameRace', 'gameRace')
           ->join('gameRace.faction', 'gameFaction')
           ->join('character.realm', 'realm')
           ->add('where', $whereClause)
           ->setMaxResults(100);

        $qb->setParameter('minLevel', $query->getSearchCriteria()->getMinLevel())
           ->setParameter('maxLevel', $query->getSearchCriteria()->getMaxLevel())
           ->setParameter('onDateTime', $query->getOnDateTime())
           ->setParameter('name', '%' . $query->getSearchCriteria()->getName() . '%');

        if ($guildParamRequired)
            $qb->setParameter('guildId', $query->getSearchCriteria()->getGuild());

        if ($raceParamRequired)
            $qb->setParameter('gameRaceId', $query->getSearchCriteria()->getGameRace());

        if ($classParamRequired)
            $qb->setParameter('gameClassId', $query->getSearchCriteria()->getGameClass());

        if ($factionParamRequired)
            $qb->setParameter('gameFactionId', $query->getSearchCriteria()->getGameFaction());

        if ($rolesParamRequired)
            $qb->setParameter('roles', $query->getSearchCriteria()->getRoles());

        if ($claimingMemberParamRequired)
            $qb->setParameter('claimingMember', '%' . $query->getSearchCriteria()->getClaimingMember() . '%');

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
        $this->characterHydrator->setOnDateTime($query->getOnDateTime());

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::QUERY_CHARACTERS_BY_CRITERIA,
                $account,
                [
                    'accountId'      => $this->authenticationService->getCurrentContext()->isAuthenticated() ? $account : null,
                    'searchCriteria' => ActivityEvent::annotatedToSimpleObject($query->getSearchCriteria()),
                    'onDateTime'     => $query->getOnDateTime()
                ]
            )
        );

        return CharacterDTO\CharacterMapper::mapArray($characterVersions, $this->characterHydrator);
    }
}
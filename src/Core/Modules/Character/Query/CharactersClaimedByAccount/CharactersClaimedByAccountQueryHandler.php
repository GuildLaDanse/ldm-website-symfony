<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\CharactersClaimedByAccount;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Character\Query\AbstractClaimedCharactersQueryHandler;
use App\Core\Modules\Character\Query\CharacterHydrator;
use App\Core\Modules\Common\MapperException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;

class CharactersClaimedByAccountQueryHandler extends AbstractClaimedCharactersQueryHandler
{
    /**
     * @param CharactersClaimedByAccountQuery $query
     *
     * @throws InvalidInputException
     */
    protected function validateInput(CharactersClaimedByAccountQuery $query): void
    {
        if ($query->getOnDateTime() === null)
        {
            throw new InvalidInputException("Input for " . __CLASS__ . " is not valid");
        }
    }

    /**
     * @param CharactersClaimedByAccountQuery $query
     *
     * @return array
     *
     * @throws MapperException
     * @throws InvalidInputException
     */
    public function __invoke(CharactersClaimedByAccountQuery $query): array
    {
        $this->validateInput($query);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        $em = $this->doctrine->getManager();

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
                            ->from(CharacterEntity\Claim::class, 'innerClaim')
                            ->join('innerClaim.character', 'innerCharacter')
                            ->where(
                                $innerQb->expr()->andX(
                                    $innerQb->expr()->eq('innerClaim.account', ':account'),
                                    $innerQb->expr()->orX(
                                        $innerQb->expr()->andX(
                                            $innerQb->expr()->lte('innerClaim.fromTime', ':onDateTime'),
                                            $innerQb->expr()->gt('innerClaim.endTime', ':onDateTime')
                                        ),
                                        $innerQb->expr()->andX(
                                            $innerQb->expr()->lte('innerClaim.fromTime', ':onDateTime'),
                                            $innerQb->expr()->isNull('innerClaim.endTime')
                                        )
                                    )
                                )
                            )->getDQL()
                    ),
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
            )
            ->addOrderBy('character.name')
            ->setParameter('onDateTime', $query->getOnDateTime())
            ->setParameter('account', $em->getReference(Account::class, $query->getAccountId()));

        $dbQuery = $qb->getQuery();

        $characterVersions = $dbQuery->getResult();

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::QUERY_CHARACTERS_CLAIMED_BY_ACCOUNT,
                $account,
                [
                    'accountId' => $this->authenticationService->getCurrentContext()->isAuthenticated() ? $account : null
                ]
            )
        );

        return $this->prepareCharacterResult($query->getOnDateTime(), $characterVersions);
    }
}
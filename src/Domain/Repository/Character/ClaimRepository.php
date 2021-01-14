<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Repository\Character;

use App\Domain\Entity\Character\Claim;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Claim|null find($id, $lockMode = null, $lockVersion = null)
 * @method Claim|null findOneBy(array $criteria, array $orderBy = null)
 * @method Claim[]    findAll()
 * @method Claim[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClaimRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Claim::class);
    }

    public function findActiveClaimsByCharacter(int $characterId): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('claim', 'character', 'account')
            ->from(Claim::class, 'claim')
            ->join('claim.character', 'character')
            ->join('claim.account', 'account')
            ->where('character.id = ?1')
            ->andWhere('claim.fromTime IS NOT NULL')
            ->andWhere('claim.endTime IS NULL')
            ->setParameter(1, $characterId)
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?TestEntity
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
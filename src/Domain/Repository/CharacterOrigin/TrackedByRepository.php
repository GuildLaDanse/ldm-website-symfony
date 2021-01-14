<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Repository\CharacterOrigin;

use App\Domain\Entity\Character\Character;
use App\Domain\Entity\CharacterOrigin\CharacterSource;
use App\Domain\Entity\CharacterOrigin\TrackedBy;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;

/**
 * @method TrackedBy|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrackedBy|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrackedBy[]    findAll()
 * @method TrackedBy[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackedByRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrackedBy::class);
    }

    /**
     * @param CharacterSource $characterSource
     * @param int $characterId
     *
     * @return array
     *
     * @throws ORMException
     */
    public function findTrackedBysForCharacter(CharacterSource $characterSource, int $characterId): array
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from(TrackedBy::class, 'trackedBy')
            ->join(CharacterSource::class, 'characterSource')
            ->where('trackedBy.character = ?1')
            ->andWhere('trackedBy.characterSource = ?2')
            ->andWhere('trackedBy.fromTime IS NOT NULL')
            ->andWhere('trackedBy.endTime IS NULL')
            ->setParameter(
                1,
                $this->getEntityManager()->getReference(Character::class, $characterId)
            )
            ->setParameter(2, $characterSource)
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
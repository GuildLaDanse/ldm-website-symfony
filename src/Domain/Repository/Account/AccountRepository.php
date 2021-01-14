<?php

namespace App\Domain\Repository\Account;

use App\Domain\Entity\Account\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Account|null find($id, $lockMode = null, $lockVersion = null)
 * @method Account|null findOneBy(array $criteria, array $orderBy = null)
 * @method Account[]    findAll()
 * @method Account[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /**
     * @param Account $account
     *
     * @throws ORMException
     */
    public function save(Account $account): void
    {
        $this->_em->persist($account);
    }

    /**
     * @param string $externalId
     *
     * @return Account
     *
     * @throws NonUniqueResultException
     */
    public function findByExternalId(string $externalId): Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.externalId = :val')
            ->setParameter('val', $externalId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string $email
     *
     * @return Account
     *
     * @throws NonUniqueResultException
     */
    public function findByEmail(string $email): Account
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.email = :val')
            ->setParameter('val', $email)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @param UserInterface $user
     * @param string $newEncodedPassword
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        /** @var Account $user */

        $user->setPassword($newEncodedPassword);
        $user->setSalt(null);

        $this->getEntityManager()->flush();
    }
}

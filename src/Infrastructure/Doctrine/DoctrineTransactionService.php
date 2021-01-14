<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Doctrine;

use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTransactionService implements TransactionServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * DoctrineTransactionService constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function start(): void
    {
        $this->entityManager->getConnection()->beginTransaction();
    }

    /**
     * @throws ConnectionException
     */
    public function commit(): void
    {
        if ($this->entityManager->getConnection()->isConnected()
            && $this->entityManager->getConnection()->isTransactionActive()
            && !$this->entityManager->getConnection()->isRollbackOnly())
        {
            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
        }
    }

    /**
     * @throws ConnectionException
     */
    public function rollback(): void
    {
        if ($this->entityManager->getConnection()->isTransactionActive())
        {
            $this->entityManager->getConnection()->rollBack();
        }
        $this->entityManager->clear();
    }
}
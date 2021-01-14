<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Doctrine;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class RequestTransactionSubscriber implements EventSubscriberInterface
{
    /**
     * @var TransactionServiceInterface
     */
    private TransactionServiceInterface $transactionService;

    public function __construct(TransactionServiceInterface $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['startTransaction', 10],
            KernelEvents::RESPONSE => ['commitTransaction', 10],
            // In the case that both the Exception and Response events are triggered, we want to make sure the
            // transaction is rolled back before trying to commit it.
            KernelEvents::EXCEPTION => ['rollbackTransaction', 11],
        ];
    }

    public function startTransaction(): void
    {
        $this->transactionService->start();
    }

    public function commitTransaction(): void
    {
        $this->transactionService->commit();
    }

    public function rollbackTransaction(): void
    {
        $this->transactionService->rollback();
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Doctrine;

interface TransactionServiceInterface
{
    public function start(): void;
    public function commit(): void;
    public function rollback(): void;
}
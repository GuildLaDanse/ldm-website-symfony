<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Account;

use App\Domain\Entity\Account\Account;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Psr\Log\LoggerInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;

class AccountService
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Registry
     */
    private Registry $doctrine;

    /**
     * AccountService constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $accountId
     *
     * @return Account
     */
    public function getAccount($accountId)
    {
        $repo = $this->doctrine->getRepository(Account::class);

        return $repo->find($accountId);
    }

    /**
     * @param $accountId
     * @param $displayName
     * @param $email
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function updateProfile($accountId, $displayName, $email)
    {
        throw new NotImplementedException("updateProfile is not implemented");
    }
}
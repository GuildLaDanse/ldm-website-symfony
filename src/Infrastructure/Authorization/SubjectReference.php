<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;


use App\Domain\Entity\Account\Account;

class SubjectReference
{
    /**
     * @var Account
     */
    private Account $account;

    /**
     * SubjectReference constructor.
     *
     * @param Account $account
     */
    public function __construct(Account $account = null)
    {
        $this->account = $account;
    }

    /**
     * @return bool
     */
    public function isAnonymous()
    {
        return ($this->account === null);
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }
}
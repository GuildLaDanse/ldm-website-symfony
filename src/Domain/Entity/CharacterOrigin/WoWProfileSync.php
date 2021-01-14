<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\CharacterOrigin;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Account\Account;

/**
 * @ORM\Entity
 * @ORM\Table(name="WoWProfileSync", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 */
class WoWProfileSync extends CharacterSource
{
    const REPOSITORY = 'LaDanseDomainBundle:CharacterOrigin\WoWProfileSync';

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity=Account::class)
     * @ORM\JoinColumn(name="account", referencedColumnName="id", nullable=false)
     */
    protected Account $account;

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     *
     * @return WoWProfileSync
     */
    public function setAccount(Account $account): WoWProfileSync
    {
        $this->account = $account;
        return $this;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Discord;


use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Account\Account;

/**
 * @ORM\Entity
 * @ORM\Table(name="DiscordAuthCode", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 */
class DiscordAuthCode
{
    const REPOSITORY = 'LaDanseDomainBundle:Discord\DiscordAuthCode';

    const STATE_PENDING  = 'Pending';  // an auth code has been created, we wait for Discord bot to request an access token
    const STATE_CONSUMED = 'Consumed'; // an access token has been created after a request from the Discord bot
    const STATE_REMOVED  = 'Removed';  // the auth code or access token is no longer considered valid

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected string $state;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected string $nonce;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected string $authCode;

    /**
     * @var int
     *
     * @ORM\Column(name="creationDate", type="integer", nullable=false)
     */
    protected int $creationDate;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account", fetch="EAGER")
     * @ORM\JoinColumn(name="account", referencedColumnName="id", nullable=false)
     */
    protected Account $account;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getNonce(): string
    {
        return $this->nonce;
    }

    /**
     * @param string $nonce
     */
    public function setNonce($nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @param string $authCode
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }

    /**
     * @return int
     */
    public function getCreationDate(): int
    {
        return $this->creationDate;
    }

    /**
     * @param int $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * @param Account $account
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }
}

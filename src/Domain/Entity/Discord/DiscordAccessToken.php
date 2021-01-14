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
 * @ORM\Table(name="DiscordAccessToken", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 */
class DiscordAccessToken
{
    const REPOSITORY = 'LaDanseDomainBundle:Discord\DiscordAccessToken';

    const STATE_ACTIVE  = 'Active';  // the access token is valid and can be used to authenticate
    const STATE_REVOKED = 'Revoked'; // the access token was revoked by the user
    const STATE_REMOVED = 'Removed'; // the access token is no longer considered valid

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
    protected string $accessToken;

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
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
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

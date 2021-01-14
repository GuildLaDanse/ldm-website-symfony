<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity;

use App\Domain\Entity\Account\Account;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="SocialConnect", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 * @ORM\HasLifecycleCallbacks
 */
class SocialConnect
{
    const REPOSITORY = 'LaDanseDomainBundle:SocialConnect';

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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $resource;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $resourceId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $accessToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected string $refreshToken;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $connectTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected DateTime $lastRefreshTime;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=false)
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
     * @return SocialConnect
     */
    public function setId(int $id): SocialConnect
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @param string $resource
     * @return SocialConnect
     */
    public function setResource(string $resource): SocialConnect
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * @return string
     */
    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    /**
     * @param string $resourceId
     * @return SocialConnect
     */
    public function setResourceId(string $resourceId): SocialConnect
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return SocialConnect
     */
    public function setAccessToken(string $accessToken): SocialConnect
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return SocialConnect
     */
    public function setRefreshToken(string $refreshToken): SocialConnect
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getConnectTime(): DateTime
    {
        return $this->connectTime;
    }

    /**
     * @param DateTime $connectTime
     * @return SocialConnect
     */
    public function setConnectTime(DateTime $connectTime): SocialConnect
    {
        $this->connectTime = $connectTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getLastRefreshTime(): DateTime
    {
        return $this->lastRefreshTime;
    }

    /**
     * @param DateTime $lastRefreshTime
     * @return SocialConnect
     */
    public function setLastRefreshTime(DateTime $lastRefreshTime): SocialConnect
    {
        $this->lastRefreshTime = $lastRefreshTime;
        return $this;
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
     * @return SocialConnect
     */
    public function setAccount(Account $account): SocialConnect
    {
        $this->account = $account;
        return $this;
    }
}

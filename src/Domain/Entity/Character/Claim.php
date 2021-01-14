<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Character;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\VersionedEntity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Character\ClaimRepository")
 * @ORM\Table(name="CharacterClaim")
 */
class Claim extends VersionedEntity
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=false)
     */
    protected Account $account;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="characterId", referencedColumnName="id", nullable=false)
     */
    protected Character $character;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PlaysRole", mappedBy="claim", cascade={"persist", "remove"})
     */
    protected ArrayCollection $roles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set account
     *
     * @param Account|object $account
     * @return Claim
     */
    public function setAccount($account): Claim
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount(): Account
    {
        return $this->account;
    }

    /**
     * Set character
     *
     * @param Character|object $character
     * @return Claim
     */
    public function setCharacter($character): Claim
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return Character
     */
    public function getCharacter(): Character
    {
        return $this->character;
    }

    /**
     * Add roles
     *
     * @param PlaysRole $roles
     * @return Claim
     */
    public function addRole(PlaysRole $roles): Claim
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param PlaysRole $roles
     */
    public function removeRole(PlaysRole $roles): void
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function containsRole($roleName, DateTime $onDateTime): bool
    {
        /* @var $playsRole PlaysRole */
        foreach($this->getRoles() as $playsRole)
        {
            if (($playsRole->isRole($roleName))
                &&
                (($playsRole->getFromTime()->getTimestamp() <= $onDateTime->getTimestamp())
                    && ($playsRole->getEndTime() === null ||
                        ($playsRole->getEndTime()->getTimestamp() > $onDateTime->getTimestamp())))
            )
            {
                return true;
            }
        }

        return false;
    }
}

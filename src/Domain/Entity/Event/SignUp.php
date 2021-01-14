<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Event;

use App\Domain\Entity\Account\Account;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\SignUpRepository")
 * @ORM\Table(name="SignUp", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class SignUp
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="signUps")
     * @ORM\JoinColumn(name="eventId", referencedColumnName="id", nullable=false)
     */
    protected Event $event;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=false)
     */
    protected Account $account;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=false)
     */
    protected string $type;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ForRole", mappedBy="signUp", cascade={"persist", "remove"})
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
     * Set event
     *
     * @param Event $event
     * @return SignUp
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return SignUp
     */
    public function setAccount(Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Add roles
     *
     * @param ForRole $roles
     * @return SignUp
     */
    public function addRole(ForRole $roles)
    {
        $this->roles[] = $roles;

        return $this;
    }

    /**
     * Remove roles
     *
     * @param ForRole $roles
     */
    public function removeRole(ForRole $roles)
    {
        $this->roles->removeElement($roles);
    }

    /**
     * Get roles
     *
     * @return Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return SignUp
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    public function toJson()
    {
        $simpleRoles = [];

        for($i = 0; $i < $this->roles->count(); $i++)
        {
            /** @noinspection NullPointerExceptionInspection */
            $simpleRoles[] = $this->roles->get($i)->getRole();
        }

        return (object) [
            'signUpId' => $this->id,
            'type'     => $this->type,
            'roles'    => $simpleRoles
        ];
    }
}

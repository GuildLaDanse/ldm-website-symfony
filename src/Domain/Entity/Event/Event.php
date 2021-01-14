<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Event;

use App\Domain\Entity\Account\Account;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Finite\Exception\ObjectException;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachine;
use Finite\StateMachine\StateMachineInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Event\EventRepository")
 * @ORM\Table(name="Event", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 * @ORM\HasLifecycleCallbacks
 */
class Event implements StatefulInterface
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
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected string $name;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $description;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="utc_datetime", nullable=false)
     */
    protected DateTime $inviteTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="utc_datetime", nullable=false)
     */
    protected DateTime $startTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="utc_datetime", nullable=false)
     */
    protected DateTime $endTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $lastModifiedTime;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    protected string $topicId;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="SignUp", mappedBy="event", cascade={"persist", "remove"})
     */
    protected Collection $signUps;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="organiserId", referencedColumnName="id", nullable=false)
     */
    protected Account $organiser;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    private ?string $state = null;

    private StateMachine $stateMachine;

    /**
     * Event constructor.
     *
     * @throws ObjectException
     */
    public function __construct()
    {
        $this->signUps = new ArrayCollection();

        $this->initStateMachine();
    }

    /**
     * @throws ObjectException
     */
    private function initStateMachine(): void
    {
        $this->stateMachine = EventStateMachine::create($this);
        $this->stateMachine->initialize();
    }

    /**
     * @ORM\PostLoad
     *
     * @throws ObjectException
     */
    public function doPostLoad()
    {
        $this->initStateMachine();
    }

    /**
     * @ORM\PrePersist
     */
    public function doPrePersist()
    {
        $this->lastModifiedTime = new DateTime('now');
    }

    /**
     * @param int $id
     *
     * @return Event
     */
    public function setId(int $id): Event
    {
        $this->id = $id;
        return $this;
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
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Event
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set inviteTime
     *
     * @param DateTime $inviteTime
     * @return Event
     */
    public function setInviteTime($inviteTime)
    {
        $this->inviteTime = $inviteTime;

        return $this;
    }

    /**
     * Get inviteTime
     *
     * @return DateTime
     */
    public function getInviteTime()
    {
        return $this->inviteTime;
    }

    /**
     * Set startTime
     *
     * @param DateTime $startTime
     * @return Event
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime
     *
     * @return DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Add signUps
     *
     * @param SignUp $signUps
     * @return Event
     */
    public function addSignUp(SignUp $signUps)
    {
        $this->signUps[] = $signUps;

        return $this;
    }

    /**
     * Remove signUps
     *
     * @param SignUp $signUps
     */
    public function removeSignUp(SignUp $signUps)
    {
        $this->signUps->removeElement($signUps);
    }

    /**
     * Get signUps
     *
     * @return Collection
     */
    public function getSignUps(): Collection
    {
        return $this->signUps;
    }

    /**
     * Set organiser
     *
     * @param Account $organiser
     * @return Event
     */
    public function setOrganiser(Account $organiser)
    {
        $this->organiser = $organiser;

        return $this;
    }

    /**
     * Get organiser
     *
     * @return Account
     */
    public function getOrganiser()
    {
        return $this->organiser;
    }

    /**
     * Set endTime
     *
     * @param DateTime $endTime
     * @return Event
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set lastModifiedTime
     *
     * @param DateTime $lastModifiedTime
     * @return Event
     */
    public function setLastModifiedTime($lastModifiedTime)
    {
        $this->lastModifiedTime = $lastModifiedTime;

        return $this;
    }

    /**
     * Get lastModifiedTime
     *
     * @return DateTime
     */
    public function getLastModifiedTime()
    {
        return $this->lastModifiedTime;
    }

    /**
     * Set topicId
     *
     * @param string $topicId
     * @return Event
     */
    public function setTopicId($topicId)
    {
        $this->topicId = $topicId;

        return $this;
    }

    /**
     * Get topicId
     *
     * @return string 
     */
    public function getTopicId()
    {
        return $this->topicId;
    }

    /**
     * Get state
     *
     * @param string|null $state
     *
     * @return Event
     */
    public function setFiniteState($state): Event
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFiniteState(): ?String
    {
        return $this->state;
    }

    /**
     * @return StateMachineInterface
     */
    public function getStateMachine()
    {
        return $this->stateMachine;
    }

    public function toJson()
    {
        return (object) [
            'eventId'     => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'inviteTime'  => $this->inviteTime->format(DateTime::ATOM),
            'startTime'   => $this->startTime->format(DateTime::ATOM),
            'endTime'     => $this->endTime->format(DateTime::ATOM),
            'organiserId' => $this->organiser->getId(),
            'state'       => $this->state
        ];
    }
}

<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Forum;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Account\Account;

/**
 * Forum
 *
 * @ORM\Entity(repositoryClass="App\Repository\Forum\ForumRepository")
 * @ORM\Table(name="Forum", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class Forum
{
    /**
     * @var string
     *
     * @ORM\Column(name="forumId", type="guid")
     * @ORM\Id
     */
    private string $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text")
     */
    private string $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private string $description;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="lastPostDate", type="datetime", nullable=true)
     */
    private DateTime $lastPostDate;

    /**
     * @var Topic
     *
     * @ORM\ManyToOne(targetEntity="Topic")
     * @ORM\JoinColumn(name="lastPostTopic", referencedColumnName="topicId", nullable=true)
     */
    private Topic $lastPostTopic;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="lastPostPoster", referencedColumnName="id", nullable=true)
     */
    private Account $lastPostPoster;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Topic", mappedBy="forum", cascade={"persist", "remove"})
     */
    protected ArrayCollection $topics;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     * @return Forum
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Forum
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
     * @return Forum
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
     * @return DateTime
     */
    public function getLastPostDate()
    {
        return $this->lastPostDate;
    }

    /**
     * @param DateTime $lastPostDate
     * @return Forum
     */
    public function setLastPostDate($lastPostDate)
    {
        $this->lastPostDate = $lastPostDate;

        return $this;
    }

    /**
     * @return Topic
     */
    public function getLastPostTopic()
    {
        return $this->lastPostTopic;
    }

    /**
     * @param Topic $lastPostTopic
     *
     * @return Forum
     */
    public function setLastPostTopic(Topic $lastPostTopic)
    {
        $this->lastPostTopic = $lastPostTopic;

        return $this;
    }

    /**
     * @return Account
     */
    public function getLastPostPoster()
    {
        return $this->lastPostPoster;
    }

    /**
     * @param Account $lastPostPoster
     *
     * @return Forum
     */
    public function setLastPostPoster(Account $lastPostPoster)
    {
        $this->lastPostPoster = $lastPostPoster;

        return $this;
    }

    /**
     * Add topics
     *
     * @param Topic $topics
     * @return Forum
     */
    public function addTopic(Topic $topics)
    {
        $this->topics[] = $topics;

        return $this;
    }

    /**
     * Remove topics
     *
     * @param Topic $topics
     */
    public function removeTopic(Topic $topics)
    {
        $this->topics->removeElement($topics);
    }

    /**
     * Get topics
     *
     * @return Collection
     */
    public function getTopics()
    {
        return $this->topics;
    }
}

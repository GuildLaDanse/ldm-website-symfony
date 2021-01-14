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
 * Topic
 *
 * @ORM\Entity(repositoryClass="App\Repository\Forum\TopicRepository")
 * @ORM\Table(name="Topic", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class Topic
{
    /**
     * @var string
     *
     * @ORM\Column(name="topicId", type="guid")
     * @ORM\Id
     */
    private string $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="postDate", type="datetime")
     */
    private DateTime $createDate;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="posterId", referencedColumnName="id", nullable=false)
     */
    private Account $creator;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private string $subject;

    /**
     * @var Forum
     *
     * @ORM\ManyToOne(targetEntity="Forum", inversedBy="topics")
     * @ORM\JoinColumn(name="forumId", referencedColumnName="forumId", nullable=true)
     */
    private Forum $forum;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Post", mappedBy="topic", cascade={"persist", "remove"})
     */
    protected ArrayCollection $posts;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="lastPostDate", type="datetime", nullable=true)
     */
    private DateTime $lastPostDate;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="lastPostPoster", referencedColumnName="id", nullable=true)
     */
    private Account $lastPostPoster;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     * @return Topic
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
     * Set createDate
     *
     * @param DateTime $createDate
     * @return Topic
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Topic
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set creator
     *
     * @param Account $creator
     * @return Topic
     */
    public function setCreator(Account $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return Account
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set forum
     *
     * @param Forum $forum
     * @return Topic
     */
    public function setForum(Forum $forum = null)
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * Get forum
     *
     * @return Forum
     */
    public function getForum()
    {
        return $this->forum;
    }

    /**
     * Add posts
     *
     * @param Post $posts
     * @return Topic
     */
    public function addPost(Post $posts)
    {
        $this->posts[] = $posts;

        return $this;
    }

    /**
     * Remove posts
     *
     * @param Post $posts
     */
    public function removePost(Post $posts)
    {
        $this->posts->removeElement($posts);
    }

    /**
     * Get posts
     *
     * @return Collection
     */
    public function getPosts()
    {
        return $this->posts;
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
     * @return Topic
     */
    public function setLastPostDate($lastPostDate)
    {
        $this->lastPostDate = $lastPostDate;

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
     * @return Topic
     */
    public function setLastPostPoster(Account $lastPostPoster)
    {
        $this->lastPostPoster = $lastPostPoster;

        return $this;
    }
}

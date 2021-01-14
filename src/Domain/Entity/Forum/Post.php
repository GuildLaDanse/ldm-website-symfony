<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Forum;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Account\Account;

/**
 * Post
 *
 * @ORM\Entity(repositoryClass="App\Repository\Forum\PostRepository")
 * @ORM\Table(name="Post", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class Post
{
    /**
     * @var string
     *
     * @ORM\Column(name="postId", type="guid")
     * @ORM\Id
     */
    private string $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="postDate", type="datetime")
     */
    private DateTime $postDate;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="posterId", referencedColumnName="id", nullable=true)
     */
    private Account $poster;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private string $message;

    /**
     * @var Topic
     *
     * @ORM\ManyToOne(targetEntity="Topic", inversedBy="posts")
     * @ORM\JoinColumn(name="topicId", referencedColumnName="topicId", nullable=true)
     */
    private Topic $topic;

    /**
     * Set id
     *
     * @param string $id
     * @return Post
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
     * Set postDate
     *
     * @param DateTime $postDate
     * @return Post
     */
    public function setPostDate($postDate)
    {
        $this->postDate = $postDate;

        return $this;
    }

    /**
     * Get postDate
     *
     * @return DateTime
     */
    public function getPostDate()
    {
        return $this->postDate;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Post
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set poster
     *
     * @param Account $poster
     * @return Post
     */
    public function setPoster(Account $poster = null)
    {
        $this->poster = $poster;

        return $this;
    }

    /**
     * Get poster
     *
     * @return Account
     */
    public function getPoster()
    {
        return $this->poster;
    }

    /**
     * Set topic
     *
     * @param Topic $topic
     * @return Post
     */
    public function setTopic(Topic $topic = null)
    {
        $this->topic = $topic;

        return $this;
    }

    /**
     * Get topic
     *
     * @return Topic
     */
    public function getTopic()
    {
        return $this->topic;
    }
}

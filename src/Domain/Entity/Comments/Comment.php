<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Comments;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Account\Account;

/**
 * Post
 *
 * @ORM\Entity(repositoryClass="App\Repository\Comments\CommentRepository")
 * @ORM\Table(name="Comment", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class Comment
{
    /**
     * @var string
     *
     * @ORM\Column(name="commentId", type="guid")
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
     * @var CommentGroup
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Comments\CommentGroup", inversedBy="comments")
     * @ORM\JoinColumn(name="groupId", referencedColumnName="groupId", nullable=true)
     */
    private CommentGroup $group;

    /**
     * Get id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Set postDate
     *
     * @param DateTime $postDate
     * @return Comment
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
     * @return Comment
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
     * @return Comment
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
     * Set CommentGroup
     *
     * @param CommentGroup $group
     * @return Comment
     */
    public function setGroup(CommentGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get CommentGroup
     *
     * @return CommentGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return Comment
     */
    public function setId(string $id): Comment
    {
        $this->id = $id;

        return $this;
    }
}

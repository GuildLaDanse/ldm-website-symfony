<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Forum;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Account\Account;

/**
 * UnreadPost
 *
 * @ORM\Table(name="UnreadPost", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 * @ORM\Entity
 */
class UnreadPost
{
    const REPOSITORY = 'LaDanseDomainBundle:Forum\UnreadPost';

    /**
     * @var string
     *
     * @ORM\Column(name="unreadId", type="guid")
     * @ORM\Id
     */
    private string $id;

    /**
     * @var Account
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\Account")
     * @ORM\JoinColumn(name="accountId", referencedColumnName="id", nullable=false)
     */
    private Account $account;

    /**
     * @var Post
     *
     * @ORM\ManyToOne(targetEntity="Post")
     * @ORM\JoinColumn(name="postId", referencedColumnName="postId", nullable=false)
     */
    private Post $post;

    /**
     * Set id
     *
     * @param string $id
     * @return UnreadPost
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
     * Set account
     *
     * @param Account $account
     * @return UnreadPost
     */
    public function setAccount(Account $account)
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
     * Set post
     *
     * @param Post $post
     * @return UnreadPost
     */
    public function setPost(Post $post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return Post
     */
    public function getPost()
    {
        return $this->post;
    }
}

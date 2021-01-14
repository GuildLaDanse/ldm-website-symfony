<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum\DTO;

use App\Core\Modules\Common\AccountReference;
use DateTime;
use JMS\Serializer\Annotation\SerializedName;

class TopicEntry
{
    /**
     * @SerializedName("id")
     *
     * @var string
     */
    protected string $id;

    /**
     * @SerializedName("subject")
     *
     * @var string
     */
    protected string $subject;

    /**
     * @SerializedName("createDate")
     *
     * @var DateTime
     */
    protected DateTime $createDate;

    /**
     * @SerializedName("creatorRef")
     *
     * @var AccountReference
     */
    protected AccountReference $creatorRef;

    /**
     * @SerializedName("lastPost")
     *
     * @var LastPostEntry
     */
    protected LastPostEntry $lastPost;

    public function __construct($id,
                                $subject,
                                DateTime $createDate,
                                AccountReference $creatorRef,
                                LastPostEntry $lastPost)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->createDate = $createDate;
        $this->creatorRef = $creatorRef;
        $this->lastPost = $lastPost;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @return AccountReference
     */
    public function getCreatorRef()
    {
        return $this->creatorRef;
    }

    /**
     * @return LastPostEntry
     */
    public function getLastPost()
    {
        return $this->lastPost;
    }
}
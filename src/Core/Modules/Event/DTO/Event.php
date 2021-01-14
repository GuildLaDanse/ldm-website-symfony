<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\DTO;

use App\Core\Modules\Common\AccountReference;
use App\Core\Modules\Common\CommentGroupReference;
use DateTime;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class Event
{
    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     *
     * @var int
     */
    protected int $id;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("name")
     *
     * @var string
     */
    protected string $name;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("description")
     *
     * @var string
     */
    protected string $description;

    /**
     * @Serializer\Type(AccountReference::class)
     * @Serializer\SerializedName("organiserRef")
     *
     * @var AccountReference
     */
    protected AccountReference $organiser;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("inviteTime")
     *
     * @var DateTime
     */
    protected DateTime $inviteTime;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("startTime")
     *
     * @var DateTime
     */
    protected DateTime $startTime;

    /**
     * @Serializer\SerializedName("endTime")
     *
     * @var DateTime
     */
    protected DateTime $endTime;

    /**
     * @Serializer\SerializedName("state")
     *
     * @var string
     */
    protected string $state;

    /**
     * @Serializer\SerializedName("commentGroupRef")
     *
     * @var CommentGroupReference
     */
    protected CommentGroupReference $commentGroup;

    /**
     * @Serializer\SerializedName("signUps")
     *
     * @var array
     */
    protected array $signUps;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Event
     */
    public function setName($name): Event
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Event
     */
    public function setDescription($description): Event
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return AccountReference
     */
    public function getOrganiser(): AccountReference
    {
        return $this->organiser;
    }

    /**
     * @param AccountReference $organiser
     *
     * @return Event
     */
    public function setOrganiser(AccountReference $organiser): Event
    {
        $this->organiser = $organiser;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getInviteTime(): DateTime
    {
        return $this->inviteTime;
    }

    /**
     * @param DateTime $inviteTime
     *
     * @return Event
     */
    public function setInviteTime(DateTime $inviteTime): Event
    {
        $this->inviteTime = $inviteTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getStartTime(): DateTime
    {
        return $this->startTime;
    }

    /**
     * @param DateTime $startTime
     *
     * @return Event
     */
    public function setStartTime(DateTime $startTime): Event
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param DateTime $endTime
     *
     * @return Event
     */
    public function setEndTime(DateTime $endTime): Event
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     *
     * @return Event
     */
    public function setState($state): Event
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return CommentGroupReference
     */
    public function getCommentGroup(): CommentGroupReference
    {
        return $this->commentGroup;
    }

    /**
     * @param CommentGroupReference $commentGroup
     *
     * @return Event
     */
    public function setCommentGroup($commentGroup): Event
    {
        $this->commentGroup = $commentGroup;
        return $this;
    }

    /**
     * @param $signUpId
     *
     * @return SignUp|null
     */
    public function getSignUpForId($signUpId): ?SignUp
    {
        foreach($this->signUps as $signUp)
        {
            /** @var SignUp $signUp */
            if ($signUp->getId() == $signUpId)
                return $signUp;
        }

        return null;
    }

    /**
     * @param $accountId
     *
     * @return SignUp|null
     */
    public function getSignUpForAccountId($accountId): ?SignUp
    {
        foreach($this->signUps as $signUp)
        {
            /** @var SignUp $signUp */
            if ($signUp->getAccount()->getId() == $accountId)
                return $signUp;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getSignUps(): array
    {
        return $this->signUps;
    }

    /**
     * @param array $signUps
     *
     * @return Event
     */
    public function setSignUps($signUps): Event
    {
        $this->signUps = $signUps;
        return $this;
    }
}
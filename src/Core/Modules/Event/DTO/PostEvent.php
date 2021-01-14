<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\DTO;

use App\Core\Modules\Common\IntegerReference;
use DateTime;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class PostEvent
{
    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("name")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    private string $name;

    /**
     * @Serializer\Type("string")
     * @Serializer\SerializedName("description")
     *
     * @var string
     */
    private string$description;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("inviteTime")
     *
     * @Assert\NotNull()
     *
     * @var DateTime
     */
    private DateTime $inviteTime;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("startTime")
     *
     * @Assert\NotNull()
     *
     * @var DateTime
     */
    private DateTime $startTime;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("endTime")
     *
     * @Assert\NotNull()
     *
     * @var DateTime
     */
    private DateTime $endTime;

    /**
     * @Serializer\Type(IntegerReference::class)
     * @Serializer\SerializedName("organiserReference")
     *
     * @Assert\NotNull()
     *
     * @var IntegerReference
     */
    private IntegerReference $organiserReference;

    public function __construct()
    {
        $this->description = "";
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return PostEvent
     */
    public function setName($name): PostEvent
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return PostEvent
     */
    public function setDescription($description): PostEvent
    {
        $this->description = $description;
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
     * @return PostEvent
     */
    public function setInviteTime(DateTime $inviteTime): PostEvent
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
     * @return PostEvent
     */
    public function setStartTime(DateTime $startTime): PostEvent
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndTime(): DateTime
    {
        return $this->endTime;
    }

    /**
     * @param DateTime $endTime
     *
     * @return PostEvent
     */
    public function setEndTime(DateTime $endTime): PostEvent
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @return IntegerReference
     */
    public function getOrganiserReference(): IntegerReference
    {
        return $this->organiserReference;
    }

    /**
     * @param IntegerReference $organiserReference
     *
     * @return PostEvent
     */
    public function setOrganiserReference(IntegerReference $organiserReference): PostEvent
    {
        $this->organiserReference = $organiserReference;
        return $this;
    }
}
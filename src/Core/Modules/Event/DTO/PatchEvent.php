<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\DTO;

use DateTime;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class PatchEvent
{
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
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("endTime")
     *
     * @var DateTime
     */
    protected DateTime $endTime;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     */
    public function setInviteTime($inviteTime)
    {
        $this->inviteTime = $inviteTime;
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
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
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
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }
}
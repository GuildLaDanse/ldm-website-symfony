<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

abstract class VersionedEntity
{
    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", length=255, nullable=false)
     */
    protected DateTime $fromTime;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", length=255, nullable=true)
     */
    protected ?DateTime $endTime;

    /**
     * Set fromTime
     *
     * @param DateTime $fromTime
     * @return $this
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;

        return $this;
    }

    /**
     * Get fromTime
     *
     * @return DateTime
     */
    public function getFromTime(): DateTime
    {
        return $this->fromTime;
    }

    /**
     * Set endTime
     *
     * @param DateTime $endTime
     *
     * @return $this
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return DateTime|null
     */
    public function getEndTime(): ?DateTime
    {
        return $this->endTime;
    }

    /**
     * Return true if the given date is within the period of this version
     *
     * @param DateTime $onDateTime
     *
     * @return bool
     */
    public function isVersionActiveOn(DateTime $onDateTime): bool
    {
        if (($this->getFromTime() <= $onDateTime)
            &&
            (($this->getEndTime() > $onDateTime) || is_null($this->getEndTime())))
        {
            return true;
        }

        return false;
    }
}

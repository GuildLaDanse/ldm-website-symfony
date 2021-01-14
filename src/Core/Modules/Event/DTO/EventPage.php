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
class EventPage
{
    /**
     * @Serializer\Type("array<App\Modules\Event\DTO\Event>")
     * @Serializer\SerializedName("events")
     *
     * @var array
     */
    private array $events;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("previousTimestamp")
     *
     * @var DateTime
     */
    private DateTime $previousTimestamp;

    /**
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("nextTimestamp")
     *
     * @var DateTime
     */
    private DateTime $nextTimestamp;

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    /**
     * @param array $events
     *
     * @return EventPage
     */
    public function setEvents(array $events): EventPage
    {
        $this->events = $events;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPreviousTimestamp(): DateTime
    {
        return $this->previousTimestamp;
    }

    /**
     * @param DateTime $previousTimestamp
     *
     * @return EventPage
     */
    public function setPreviousTimestamp(DateTime $previousTimestamp): EventPage
    {
        $this->previousTimestamp = $previousTimestamp;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getNextTimestamp(): DateTime
    {
        return $this->nextTimestamp;
    }

    /**
     * @param DateTime $nextTimestamp
     *
     * @return EventPage
     */
    public function setNextTimestamp(DateTime $nextTimestamp): EventPage
    {
        $this->nextTimestamp = $nextTimestamp;
        return $this;
    }
}
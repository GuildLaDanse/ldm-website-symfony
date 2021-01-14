<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PutEvent;

use App\Core\Modules\Event\DTO as EventDTO;

class PutEventCommand
{
    /**
     * @var int
     */
    private int $eventId;

    /**
     * @var EventDTO\PutEvent
     */
    private EventDTO\PutEvent $putEventDto;

    public function __construct(int $eventId, EventDTO\PutEvent $putEventDto)
    {
        $this->eventId = $eventId;
        $this->putEventDto = $putEventDto;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }
    /**
     * @return EventDTO\PutEvent
     */
    public function getPutEventDto(): EventDTO\PutEvent
    {
        return $this->putEventDto;
    }
}
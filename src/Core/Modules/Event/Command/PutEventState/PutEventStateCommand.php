<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PutEventState;

use App\Core\Modules\Event\DTO as EventDTO;

class PutEventStateCommand
{
    /**
     * @var int
     */
    private int $eventId;

    /**
     * @var EventDTO\PutEventState
     */
    private EventDTO\PutEventState $putEventState;

    public function __construct(int $eventId, EventDTO\PutEventState $putEventState)
    {
        $this->eventId = $eventId;
        $this->putEventState = $putEventState;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @return EventDTO\PutEventState
     */
    public function getPutEventState(): EventDTO\PutEventState
    {
        return $this->putEventState;
    }
}
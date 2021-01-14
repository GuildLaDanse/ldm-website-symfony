<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\DeleteEvent;

class DeleteEventCommand
{
    /**
     * @var int
     */
    private int $eventId;

    /**
     * @param int $eventId
     */
    public function __construct(int $eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
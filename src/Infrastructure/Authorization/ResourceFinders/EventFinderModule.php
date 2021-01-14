<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization\ResourceFinders;

use App\Core\Modules\Event\DTO\Event;
use App\Core\Modules\Event\EventService;
use Psr\Log\LoggerInterface;

class EventFinderModule implements ResourceFinderModule
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var EventService
     */
    private EventService $eventService;

    /**
     * @param LoggerInterface $logger
     * @param EventService $eventService
     */
    public function __construct(LoggerInterface $logger, EventService $eventService)
    {
        $this->logger = $logger;
        $this->eventService = $eventService;
    }

    /**
     * @param $resourceId
     *
     * @return Event
     */
    function findResourceById($resourceId)
    {
        return $this->eventService->getEventById($resourceId);
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event;

use App\Infrastructure\Messenger\CommandBusTrait;
use App\Infrastructure\Messenger\QueryBusTrait;
use App\Core\Modules\Event\Command\DeleteEvent\DeleteEventCommand;
use App\Core\Modules\Event\Command\DeleteSignUp\DeleteSignUpCommand;
use App\Core\Modules\Event\Command\NotifyEventToday\NotifyEventTodayCommand;
use App\Core\Modules\Event\Command\PostEvent\PostEventCommand;
use App\Core\Modules\Event\Command\PostSignUp\PostSignUpCommand;
use App\Core\Modules\Event\Command\PutEvent\PutEventCommand;
use App\Core\Modules\Event\Command\PutEventState\PutEventStateCommand;
use App\Core\Modules\Event\Command\PutSignUp\PutSignUpCommand;
use App\Core\Modules\Event\Query\GetAllEventsPaged\GetAllEventsPagedQuery;
use App\Core\Modules\Event\Query\GetEventById\GetEventByIdQuery;
use DateTime;
use Psr\Log\LoggerInterface;
use App\Core\Modules\Event\DTO as EventDTO;
use Symfony\Component\Messenger\MessageBusInterface;

class EventService
{
    use CommandBusTrait;
    use QueryBusTrait;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     * @param MessageBusInterface $commandBus
     * @param MessageBusInterface $queryBus
     */
    public function __construct(
        LoggerInterface $logger,
        MessageBusInterface $commandBus,
        MessageBusInterface $queryBus)
    {
        $this->logger = $logger;
        $this->_commandBus = $commandBus;
        $this->_queryBus = $queryBus;
    }

    /**
     * Return all events.
     *
     * The result is sorted by invite time (ascending) and limited to 28 days starting from $fromTime (included)
     *
     * @param DateTime $fromTime
     *
     * @return EventDTO\EventPage
     */
    public function getAllEventsPaged(DateTime $fromTime): EventDTO\EventPage
    {
        $query = new GetAllEventsPagedQuery($fromTime);

        return $this->dispatchQuery($query);
    }

    /**
     * Return the event with the given id
     *
     * @param int $eventId id of event to retrieve
     *
     * @return EventDTO\Event
     */
    public function getEventById($eventId): EventDTO\Event
    {
        $query = new GetEventByIdQuery($eventId);

        return $this->dispatchQuery($query);
    }

    /**
     * Create a new event
     *
     * @param EventDTO\PostEvent $postEvent
     *
     * @return EventDTO\Event
     */
    public function postEvent(EventDTO\PostEvent $postEvent): EventDTO\Event
    {
        $command = new PostEventCommand($postEvent);

        return $this->dispatchCommand($command);
    }

    /**
     * Update an existing event
     *
     * @param int $eventId
     * @param EventDTO\PutEvent $putEvent
     *
     * @return EventDTO\Event
     */
    public function putEvent(int $eventId, EventDTO\PutEvent $putEvent): EventDTO\Event
    {
        $command = new PutEventCommand($eventId, $putEvent);

        return $this->dispatchCommand($command);
    }

    /**
     * Update the state of an existing event
     *
     * @param int $eventId
     * @param EventDTO\PutEventState $putEventState
     *
     * @return EventDTO\Event
     */
    public function putEventState($eventId, EventDTO\PutEventState $putEventState): EventDTO\Event
    {
        $command = new PutEventStateCommand($eventId, $putEventState);

        return $this->dispatchCommand($command);
    }

    /**
     * Delete an existing event
     *
     * @param $eventId
     */
    public function deleteEvent($eventId): void
    {
        $command = new DeleteEventCommand($eventId);

        $this->dispatchCommand($command);
    }

    /**
     * Create a new sign up for an existing event
     *
     * @param $eventId
     * @param EventDTO\PostSignUp $postSignUp
     *
     * @return EventDTO\Event
     */
    public function postSignUp($eventId, EventDTO\PostSignUp $postSignUp): EventDTO\Event
    {
        $command = new PostSignUpCommand($eventId, $postSignUp);

        return $this->dispatchCommand($command);
    }

    /**
     * Update an existing sign up
     *
     * @param $eventId
     * @param $signUpId
     *
     * @param EventDTO\PutSignUp $putSignUp
     *
     * @return EventDTO\Event
     */
    public function putSignUp($eventId, $signUpId, EventDTO\PutSignUp $putSignUp): EventDTO\Event
    {
        $command = new PutSignUpCommand($eventId, $signUpId, $putSignUp);

        return $this->dispatchCommand($command);
    }

    /**
     * Remove an existing sign up
     *
     * @param $eventId
     * @param $signUpId
     *
     * @return EventDTO\Event
     */
    public function deleteSignUp($eventId, $signUpId): EventDTO\Event
    {
        $command = new DeleteSignUpCommand($eventId, $signUpId);

        return $this->dispatchCommand($command);
    }

    /**
     * Create notification events for all events that happen today
     */
    public function notifyEventsToday(): void
    {
        $command = new NotifyEventTodayCommand();

        $this->dispatchCommand($command);
    }
}
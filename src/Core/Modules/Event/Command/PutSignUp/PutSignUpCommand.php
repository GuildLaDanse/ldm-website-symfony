<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PutSignUp;

use App\Core\Modules\Event\DTO as EventDTO;

class PutSignUpCommand
{
    /**
     * @var int
     */
    private int $eventId;

    /**
     * @var int
     */
    private int $signUpId;

    /**
     * @var EventDTO\PutSignUp
     */
    private EventDTO\PutSignUp $putSignUp;

    public function __construct(int $eventId, int $signUpId, EventDTO\PutSignUp $putSignUp)
    {
        $this->eventId = $eventId;
        $this->signUpId = $signUpId;
        $this->putSignUp = $putSignUp;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @return int
     */
    public function getSignUpId(): int
    {
        return $this->signUpId;
    }

    /**
     * @return EventDTO\PutSignUp
     */
    public function getPutSignUp(): EventDTO\PutSignUp
    {
        return $this->putSignUp;
    }
}
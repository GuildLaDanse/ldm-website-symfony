<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\DeleteSignUp;

class DeleteSignUpCommand
{
    /**
     * @var int
     */
    private int $eventId;

    /**
     * @var int
     */
    private int $signUpId;

    public function __construct(int $eventId, int $signUpId)
    {
        $this->eventId = $eventId;
        $this->signUpId = $signUpId;
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
}
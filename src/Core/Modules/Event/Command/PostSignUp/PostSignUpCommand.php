<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Command\PostSignUp;

use App\Core\Modules\Event\DTO\PostSignUp;

class PostSignUpCommand
{
    /**
     * @var int
     */
    private int $eventId;

    /**
     * @var PostSignUp
     */
    private PostSignUp $postSignUp;

    /**
     * @param int $eventId
     * @param PostSignUp $postSignUp
     */
    public function __construct(int $eventId, PostSignUp $postSignUp)
    {
        $this->eventId = $eventId;
        $this->postSignUp = $postSignUp;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @return PostSignUp
     */
    public function getPostSignUp(): PostSignUp
    {
        return $this->postSignUp;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event;

use App\Infrastructure\Modules\ServiceException;
use Exception;

class EventDoesNotExistException extends ServiceException
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }
}
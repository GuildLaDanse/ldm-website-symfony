<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Authorization;

use App\Infrastructure\Modules\ServiceException;
use Exception;

class NotAuthorizedException extends ServiceException
{
    public function __construct($message, Exception $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}
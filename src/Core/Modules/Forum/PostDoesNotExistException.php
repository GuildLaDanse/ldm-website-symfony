<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum;

use Exception;

/**
 * Class PostDoesNotExistException
 * @package LaDanse\ForumBundle\Service
 */
class PostDoesNotExistException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     */
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }
}

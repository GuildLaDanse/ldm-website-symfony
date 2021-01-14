<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Modules;

class UUIDUtils
{
    /**
     * @return string
     */
    public static function createUUID(): string
    {
        return md5(uniqid('', true));
    }
}
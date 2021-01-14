<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\CreateGuildSyncSession;

use App\Core\Modules\Common\StringReference;

class CreateGuildSyncSessionCommand
{
    /**
     * @var StringReference
     */
    private StringReference $guildId;

    /**
     * CreateGuildSyncSessionCommand constructor.
     * @param StringReference $guildId
     */
    public function __construct(StringReference $guildId)
    {
        $this->guildId = $guildId;
    }

    /**
     * @return StringReference
     */
    public function getGuildId(): StringReference
    {
        return $this->guildId;
    }
}
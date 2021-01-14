<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\Command\PostRealm;

use App\Core\Modules\GameData\DTO as GameDataDTO;

class PostRealmCommand
{
    /**
     * @var GameDataDTO\PatchRealm
     */
    private GameDataDTO\PatchRealm $patchRealm;

    /**
     * PostRealmCommand constructor.
     * @param GameDataDTO\PatchRealm $patchRealm
     */
    public function __construct(GameDataDTO\PatchRealm $patchRealm)
    {
        $this->patchRealm = $patchRealm;
    }

    /**
     * @return GameDataDTO\PatchRealm
     */
    public function getPatchRealm(): GameDataDTO\PatchRealm
    {
        return $this->patchRealm;
    }
}
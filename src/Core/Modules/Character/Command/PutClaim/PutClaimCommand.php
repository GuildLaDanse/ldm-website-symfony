<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\PutClaim;

use App\Core\Modules\Character\DTO as CharacterDTO;

class PutClaimCommand
{
    /**
     * @var int
     */
    private int $characterId;

    /**
     * @var CharacterDTO\PatchClaim
     */
    private CharacterDTO\PatchClaim $patchClaim;

    /**
     * PutClaimCommand constructor.
     * @param int $characterId
     * @param CharacterDTO\PatchClaim $patchClaim
     */
    public function __construct(int $characterId, CharacterDTO\PatchClaim $patchClaim)
    {
        $this->characterId = $characterId;
        $this->patchClaim = $patchClaim;
    }

    /**
     * @return int
     */
    public function getCharacterId(): int
    {
        return $this->characterId;
    }

    /**
     * @return CharacterDTO\PatchClaim
     */
    public function getPatchClaim(): CharacterDTO\PatchClaim
    {
        return $this->patchClaim;
    }
}
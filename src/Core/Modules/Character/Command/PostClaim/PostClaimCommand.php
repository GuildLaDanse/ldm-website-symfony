<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\PostClaim;

use App\Core\Modules\Character\DTO as CharacterDTO;

class PostClaimCommand
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
     * @var int
     */
    private int $accountId;

    /**
     * @param int $characterId
     * @param int $accountId
     * @param CharacterDTO\PatchClaim $patchClaim
     */
    public function __construct(int $characterId, int $accountId, CharacterDTO\PatchClaim $patchClaim)
    {
        $this->characterId = $characterId;
        $this->accountId = $accountId;
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

    /**
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->accountId;
    }
}
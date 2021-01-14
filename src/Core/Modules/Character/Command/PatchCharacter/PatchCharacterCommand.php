<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\PatchCharacter;

use App\Core\Modules\Character\CharacterSession;
use App\Core\Modules\Character\DTO as CharacterDTO;

class PatchCharacterCommand
{
    /**
     * @var int
     */
    private int $characterId;

    /** @var CharacterDTO\PatchCharacter */
    private CharacterDTO\PatchCharacter $patchCharacter;

    /**
     * @var CharacterSession
     */
    private CharacterSession $characterSession;

    /**
     * PatchCharacterCommand constructor.
     * @param int $characterId
     * @param CharacterDTO\PatchCharacter $patchCharacter
     * @param CharacterSession $characterSession
     */
    public function __construct(int $characterId, CharacterDTO\PatchCharacter $patchCharacter, CharacterSession $characterSession)
    {
        $this->characterId = $characterId;
        $this->patchCharacter = $patchCharacter;
        $this->characterSession = $characterSession;
    }

    /**
     * @return int
     */
    public function getCharacterId(): int
    {
        return $this->characterId;
    }

    /**
     * @return CharacterDTO\PatchCharacter
     */
    public function getPatchCharacter(): CharacterDTO\PatchCharacter
    {
        return $this->patchCharacter;
    }

    /**
     * @return CharacterSession
     */
    public function getCharacterSession(): CharacterSession
    {
        return $this->characterSession;
    }
}
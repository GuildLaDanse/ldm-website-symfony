<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\TrackCharacter;

use App\Core\Modules\Character\CharacterSession;
use App\Core\Modules\Character\DTO as CharacterDTO;

class TrackCharacterCommand
{
    /**
     * @var CharacterSession
     */
    private CharacterSession $characterSession;

    /**
     * @var CharacterDTO\PatchCharacter
     */
    private CharacterDTO\PatchCharacter $patchCharacter;

    /**
     * TrackCharacterCommand constructor.
     * @param CharacterSession $characterSession
     * @param CharacterDTO\PatchCharacter $patchCharacter
     */
    public function __construct(CharacterSession $characterSession, CharacterDTO\PatchCharacter $patchCharacter)
    {
        $this->characterSession = $characterSession;
        $this->patchCharacter = $patchCharacter;
    }

    /**
     * @return CharacterSession
     */
    public function getCharacterSession(): CharacterSession
    {
        return $this->characterSession;
    }

    /**
     * @return CharacterDTO\PatchCharacter
     */
    public function getPatchCharacter(): CharacterDTO\PatchCharacter
    {
        return $this->patchCharacter;
    }
}
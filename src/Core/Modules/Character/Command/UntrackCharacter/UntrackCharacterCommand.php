<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\UntrackCharacter;

use App\Core\Modules\Character\CharacterSession;

class UntrackCharacterCommand
{
    /**
     * @var CharacterSession
     */
    private CharacterSession $characterSession;

    /**
     * @var int
     */
    private int $characterId;

    /**
     * UntrackCharacterCommand constructor.
     * @param CharacterSession $characterSession
     * @param int $characterId
     */
    public function __construct(CharacterSession $characterSession, int $characterId)
    {
        $this->characterSession = $characterSession;
        $this->characterId = $characterId;
    }

    /**
     * @return CharacterSession
     */
    public function getCharacterSession(): CharacterSession
    {
        return $this->characterSession;
    }

    /**
     * @return int
     */
    public function getCharacterId(): int
    {
        return $this->characterId;
    }
}
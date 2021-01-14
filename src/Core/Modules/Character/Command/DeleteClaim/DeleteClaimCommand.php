<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\DeleteClaim;

class DeleteClaimCommand
{
    /**
     * @var int
     */
    private int $characterId;

    /**
     * DeleteClaimCommand constructor.
     * @param int $characterId
     */
    public function __construct(int $characterId)
    {
        $this->characterId = $characterId;
    }

    /**
     * @return int
     */
    public function getCharacterId(): int
    {
        return $this->characterId;
    }
}
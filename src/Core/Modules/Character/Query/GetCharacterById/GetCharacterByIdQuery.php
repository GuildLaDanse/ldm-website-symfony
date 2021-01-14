<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\GetCharacterById;

use DateTime;

class GetCharacterByIdQuery
{
    /**
     * @var int $characterId
     */
    private int $characterId;

    /**
     * @var DateTime $onDateTime
     */
    private DateTime $onDateTime;

    /**
     * GetCharacterByIdQuery constructor.
     * @param int $characterId
     * @param DateTime $onDateTime
     */
    public function __construct(int $characterId, DateTime $onDateTime)
    {
        $this->characterId = $characterId;
        $this->onDateTime = $onDateTime;
    }

    /**
     * @return int
     */
    public function getCharacterId(): int
    {
        return $this->characterId;
    }

    /**
     * @return DateTime
     */
    public function getOnDateTime(): DateTime
    {
        return $this->onDateTime;
    }
}
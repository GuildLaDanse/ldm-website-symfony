<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\GetAllCharactersInGuild;

use App\Core\Modules\Common\StringReference;
use DateTime;

class GetAllCharactersInGuildQuery
{
    /**
     * @var StringReference
     */
    private StringReference $guildReference;

    /**
     * @var DateTime
     */
    private DateTime $onDateTime;

    /**
     * GetAllCharactersInGuildQuery constructor.
     * @param StringReference $guildReference
     * @param DateTime $onDateTime
     */
    public function __construct(StringReference $guildReference, DateTime $onDateTime)
    {
        $this->guildReference = $guildReference;
        $this->onDateTime = $onDateTime;
    }

    /**
     * @return StringReference
     */
    public function getGuildReference(): StringReference
    {
        return $this->guildReference;
    }

    /**
     * @return DateTime
     */
    public function getOnDateTime(): DateTime
    {
        return $this->onDateTime;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\GetAllClaimedCharacters;

use DateTime;

class GetAllClaimedCharactersQuery
{
    /**
     * @var DateTime
     */
    private DateTime $onDateTime;

    /**
     * @param DateTime $onDateTime
     */
    public function __construct(DateTime $onDateTime)
    {
        $this->onDateTime = $onDateTime;
    }

    /**
     * @return DateTime
     */
    public function getOnDateTime(): DateTime
    {
        return $this->onDateTime;
    }
}
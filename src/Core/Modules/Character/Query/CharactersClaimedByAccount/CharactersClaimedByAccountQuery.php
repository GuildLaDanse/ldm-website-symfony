<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\CharactersClaimedByAccount;

use DateTime;

class CharactersClaimedByAccountQuery
{
    /**
     * @var int
     */
    private int $accountId;

    /**
     * @var DateTime
     */
    private DateTime $onDateTime;

    /**
     * CharactersClaimedByAccountQuery constructor.
     * @param int $accountId
     * @param DateTime $onDateTime
     */
    public function __construct(int $accountId, DateTime $onDateTime)
    {
        $this->accountId = $accountId;
        $this->onDateTime = $onDateTime;
    }

    /**
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->accountId;
    }

    /**
     * @return DateTime
     */
    public function getOnDateTime(): DateTime
    {
        return $this->onDateTime;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query\CharactersByCriteria;

use App\Core\Modules\Character\DTO\SearchCriteria;
use DateTime;

class CharactersByCriteriaQuery
{
    /**
     * @var SearchCriteria
     */
    private SearchCriteria $searchCriteria;

    /**
     * @var DateTime
     */
    private DateTime $onDateTime;

    /**
     * @param SearchCriteria $searchCriteria
     * @param DateTime $onDateTime
     */
    public function __construct(SearchCriteria $searchCriteria, DateTime $onDateTime)
    {
        $this->searchCriteria = $searchCriteria;
        $this->onDateTime = $onDateTime;
    }

    /**
     * @return SearchCriteria
     */
    public function getSearchCriteria(): SearchCriteria
    {
        return $this->searchCriteria;
    }

    /**
     * @return DateTime
     */
    public function getOnDateTime(): DateTime
    {
        return $this->onDateTime;
    }
}
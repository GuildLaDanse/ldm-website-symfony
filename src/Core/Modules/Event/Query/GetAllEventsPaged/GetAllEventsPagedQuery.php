<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Event\Query\GetAllEventsPaged;

use DateTime;

class GetAllEventsPagedQuery
{
    /**
     * @var DateTime
     */
    private DateTime $startOn;

    public function __construct(DateTime $startOn)
    {
        $this->startOn = $startOn;
    }

    /**
     * @return DateTime
     */
    public function getStartOn(): DateTime
    {
        return $this->startOn;
    }
}
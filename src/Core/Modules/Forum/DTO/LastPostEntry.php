<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum\DTO;

use App\Core\Modules\Common\AccountReference;
use DateTime;
use JMS\Serializer\Annotation\SerializedName;

class LastPostEntry
{
    /**
     * @SerializedName("postDate")
     *
     * @var DateTime
     */
    protected DateTime $postDate;

    /**
     * @SerializedName("posterRef")
     *
     * @var AccountReference
     */
    protected AccountReference $posterRef;

    /**
     * LastPostEntry constructor.
     *
     * @param DateTime $postDate
     * @param AccountReference $posterRef
     */
    public function __construct(DateTime $postDate,
                                AccountReference $posterRef)
    {
        $this->postDate = $postDate;
        $this->posterRef = $posterRef;
    }

    /**
     * @return DateTime
     */
    public function getPostDate()
    {
        return $this->postDate;
    }

    /**
     * @return AccountReference
     */
    public function getPosterRef()
    {
        return $this->posterRef;
    }
}
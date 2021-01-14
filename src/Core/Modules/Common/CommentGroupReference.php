<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Common;

use JMS\Serializer\Annotation\SerializedName;

class CommentGroupReference
{
    /**
     * @SerializedName("id")
     *
     * @var string
     */
    protected string $id;

    /**
     * AccountReference constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Common;

use JMS\Serializer\Annotation\SerializedName;

class AccountReference
{
    /**
     * @SerializedName("id")
     *
     * @var int
     */
    protected int $id;

    /**
     * @SerializedName("name")
     *
     * @var string
     */
    protected string $name;

    /**
     * AccountReference constructor.
     *
     * @param int $id
     * @param string $name
     */
    public function __construct($id,
                                $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
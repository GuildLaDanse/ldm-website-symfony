<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\DTO;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * @ExclusionPolicy("none")
 */
class PatchClaim
{
    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("comment")
     */
    private string $comment;

    /**
     * @var bool
     *
     * @Type("boolean")
     * @SerializedName("raider")
     */
    private bool $raider;

    /**
     * @var array
     *
     * @Type("array<string>")
     * @SerializedName("roles")
     */
    private array $roles = [];

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return PatchClaim
     */
    public function setComment($comment): PatchClaim
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRaider(): bool
    {
        return $this->raider;
    }

    /**
     * @param boolean $raider
     *
     * @return PatchClaim
     */
    public function setRaider(bool $raider): PatchClaim
    {
        $this->raider = $raider;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return PatchClaim
     */
    public function setRoles(array $roles): PatchClaim
    {
        $this->roles = $roles;
        return $this;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * @ExclusionPolicy("none")
 */
class GameFaction
{
    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("id")
     */
    protected string $id;

    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("armoryId")
     */
    protected string $armoryId;

    /**
     * @var string
     *
     * @SerializedName("name")
     */
    protected string $name;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GameFaction
     */
    public function setId(string $id) : GameFaction
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getArmoryId(): string
    {
        return $this->armoryId;
    }

    /**
     * @param string $armoryId
     * @return GameFaction
     */
    public function setArmoryId(string $armoryId) : GameFaction
    {
        $this->armoryId = $armoryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return GameFaction
     */
    public function setName(string $name) : GameFaction
    {
        $this->name = $name;
        return $this;
    }
}
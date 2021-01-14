<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use App\Core\Modules\Common\StringReference;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;

/**
 * @ExclusionPolicy("none")
 */
class GameRace
{
    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("id")
     */
    protected string $id;

    /**
     * @var integer
     *
     * @Type("integer")
     * @SerializedName("armoryId")
     */
    protected int $armoryId;

    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("name")
     */
    protected string $name;

    /**
     * @var StringReference
     *
     * @Type(StringReference::class)
     * @SerializedName("gameFactionReference")
     */
    protected StringReference $gameFactionReference;

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GameRace
     */
    public function setId(string $id) : GameRace
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getArmoryId()
    {
        return $this->armoryId;
    }

    /**
     * @param int $armoryId
     * @return GameRace
     */
    public function setArmoryId($armoryId) : GameRace
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
     * @return GameRace
     */
    public function setName(string $name) : GameRace
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return StringReference
     */
    public function getGameFactionReference(): StringReference
    {
        return $this->gameFactionReference;
    }

    /**
     * @param StringReference $gameFactionReference
     * @return GameRace
     */
    public function setGameFactionReference(StringReference $gameFactionReference): GameRace
    {
        $this->gameFactionReference = $gameFactionReference;
        return $this;
    }
}
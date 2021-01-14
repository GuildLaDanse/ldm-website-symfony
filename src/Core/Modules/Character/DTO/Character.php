<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\DTO;

use App\Core\Modules\Common\StringReference;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ExclusionPolicy("none")
 */
class Character
{
    /**
     * @var int
     *
     * @SerializedName("id")
     */
    protected int $id;

    /**
     * @var string
     * @SerializedName("name")
     */
    protected string $name;

    /**
     * @var integer
     * @SerializedName("level")
     */
    protected int $level;

    /**
     * @var StringReference
     * @SerializedName("guildReference")
     */
    protected StringReference $guildReference;

    /**
     * @var StringReference
     * @SerializedName("realmReference")
     */
    protected StringReference $realmReference;

    /**
     * @var StringReference
     * @SerializedName("gameClassReference")
     */
    protected StringReference $gameClassReference;

    /**
     * @var StringReference
     * @SerializedName("gameRaceReference")
     */
    protected StringReference $gameRaceReference;

    /**
     * @var Claim
     * @SerializedName("claim")
     */
    protected Claim $claim;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Character
     */
    public function setId(int $id): Character
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Character
     */
    public function setName(string $name): Character
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return Character
     */
    public function setLevel(int $level): Character
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return StringReference
     */
    public function getGuildReference(): StringReference
    {
        return $this->guildReference;
    }

    /**
     * @param StringReference $guildReference
     * @return Character
     */
    public function setGuildReference(StringReference $guildReference): Character
    {
        $this->guildReference = $guildReference;
        return $this;
    }

    /**
     * @return StringReference
     */
    public function getRealmReference(): StringReference
    {
        return $this->realmReference;
    }

    /**
     * @param StringReference $realmReference
     * @return Character
     */
    public function setRealmReference(StringReference $realmReference): Character
    {
        $this->realmReference = $realmReference;
        return $this;
    }

    /**
     * @return StringReference
     */
    public function getGameClassReference(): StringReference
    {
        return $this->gameClassReference;
    }

    /**
     * @param StringReference $gameClassReference
     * @return Character
     */
    public function setGameClassReference(StringReference $gameClassReference): Character
    {
        $this->gameClassReference = $gameClassReference;
        return $this;
    }

    /**
     * @return StringReference
     */
    public function getGameRaceReference(): StringReference
    {
        return $this->gameRaceReference;
    }

    /**
     * @param StringReference $gameRaceReference
     * @return Character
     */
    public function setGameRaceReference(StringReference $gameRaceReference): Character
    {
        $this->gameRaceReference = $gameRaceReference;
        return $this;
    }

    /**
     * @return Claim
     */
    public function getClaim(): Claim
    {
        return $this->claim;
    }

    /**
     * @param Claim $claim
     * @return Character
     */
    public function setClaim(Claim $claim): Character
    {
        $this->claim = $claim;
        return $this;
    }
}
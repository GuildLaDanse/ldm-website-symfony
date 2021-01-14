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
class SearchCriteria
{
    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("name")
     */
    private ?string $name = null;

    /**
     * @var integer
     *
     * @Type("integer")
     * @SerializedName("minLevel")
     */
    private int $minLevel = 1;

    /**
     * @var integer
     *
     * @Type("integer")
     * @SerializedName("maxLevel")
     */
    private int $maxLevel = 120;

    /**
     * @var integer
     *
     * @Type("integer")
     * @SerializedName("raider")
     */
    private int $raider = 0;

    /**
     * @var integer
     *
     * @Type("integer")
     * @SerializedName("claimed")
     */
    private int $claimed = 0;

    /**
     * @var string|null
     *
     * @Type("string")
     * @SerializedName("claimingMember")
     */
    private ?string $claimingMember = null;

    /**
     * @var string|null
     *
     * @Type("string")
     * @SerializedName("guild")
     */
    private ?string $guild = null;

    /**
     * @var string|null
     *
     * @Type("string")
     * @SerializedName("gameClass")
     */
    private ?string $gameClass = null;

    /**
     * @var string|null
     *
     * @Type("string")
     * @SerializedName("gameRace")
     */
    private ?string $gameRace = null;

    /**
     * @var string|null
     *
     * @Type("string")
     * @SerializedName("gameFaction")
     */
    private ?string $gameFaction = null;

    /**
     * @var array|null
     *
     * @Type("array<string>")
     * @SerializedName("roles")
     */
    private ?array $roles = null;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return SearchCriteria
     */
    public function setName(string $name): SearchCriteria
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinLevel(): int
    {
        return $this->minLevel;
    }

    /**
     * @param int $minLevel
     * @return SearchCriteria
     */
    public function setMinLevel(int $minLevel): SearchCriteria
    {
        $this->minLevel = $minLevel;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxLevel(): int
    {
        return $this->maxLevel;
    }

    /**
     * @param int $maxLevel
     * @return SearchCriteria
     */
    public function setMaxLevel(int $maxLevel): SearchCriteria
    {
        $this->maxLevel = $maxLevel;
        return $this;
    }

    /**
     * @return int
     */
    public function getRaider(): int
    {
        return $this->raider;
    }

    /**
     * @param int $raider
     * @return SearchCriteria
     */
    public function setRaider(int $raider): SearchCriteria
    {
        $this->raider = $raider;
        return $this;
    }

    /**
     * @return int
     */
    public function getClaimed(): int
    {
        return $this->claimed;
    }

    /**
     * @param int $claimed
     * @return SearchCriteria
     */
    public function setClaimed(int $claimed): SearchCriteria
    {
        $this->claimed = $claimed;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClaimingMember(): ?string
    {
        return $this->claimingMember;
    }

    /**
     * @param string|null $claimingMember
     * @return SearchCriteria
     */
    public function setClaimingMember(?string $claimingMember): SearchCriteria
    {
        $this->claimingMember = $claimingMember;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGuild(): ?string
    {
        return $this->guild;
    }

    /**
     * @param string|null $guild
     * @return SearchCriteria
     */
    public function setGuild(?string $guild): SearchCriteria
    {
        $this->guild = $guild;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGameClass(): ?string
    {
        return $this->gameClass;
    }

    /**
     * @param string|null $gameClass
     * @return SearchCriteria
     */
    public function setGameClass(?string $gameClass): SearchCriteria
    {
        $this->gameClass = $gameClass;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGameRace(): ?string
    {
        return $this->gameRace;
    }

    /**
     * @param string|null $gameRace
     * @return SearchCriteria
     */
    public function setGameRace(?string $gameRace): SearchCriteria
    {
        $this->gameRace = $gameRace;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGameFaction(): ?string
    {
        return $this->gameFaction;
    }

    /**
     * @param string|null $gameFaction
     * @return SearchCriteria
     */
    public function setGameFaction(?string $gameFaction): SearchCriteria
    {
        $this->gameFaction = $gameFaction;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array|null $roles
     * @return SearchCriteria
     */
    public function setRoles(?array $roles): SearchCriteria
    {
        $this->roles = $roles;
        return $this;
    }
}
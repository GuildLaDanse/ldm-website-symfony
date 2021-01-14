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
class Realm
{
    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("id")
     */
    private string $id;

    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("name")
     */
    private string $name;

    /**
     * @var int|null
     *
     * @Type("integer")
     * @SerializedName("gameId")
     */
    private ?int $gameId;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Realm
     */
    public function setId(string $id): Realm
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
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getGameId(): ?int
    {
        return $this->gameId;
    }

    /**
     * @param int|null $gameId
     * @return Realm
     */
    public function setGameId(?int $gameId): Realm
    {
        $this->gameId = $gameId;
        return $this;
    }
}
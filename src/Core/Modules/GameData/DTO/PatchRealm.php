<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ExclusionPolicy("none")
 */
class PatchRealm
{
    /**
     * @var string
     *
     * @Type("string")
     * @SerializedName("name")
     * @Assert\NotBlank()
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PatchRealm
     */
    public function setName(string $name): PatchRealm
    {
        $this->name = $name;
        return $this;
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
     * @return PatchRealm
     */
    public function setGameId(?int $gameId): PatchRealm
    {
        $this->gameId = $gameId;
        return $this;
    }
}
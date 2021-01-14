<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\GameData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameData\GameClassRepository")
 * @ORM\Table(name="GameClass", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class GameClass
{
    /**
     * @var string
     *
     * @ORM\Column(type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected string $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected int $armoryId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected string $name;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GameClass
     */
    public function setId(string $id): GameClass
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getArmoryId(): int
    {
        return $this->armoryId;
    }

    /**
     * @param int $armoryId
     * @return GameClass
     */
    public function setArmoryId(int $armoryId): GameClass
    {
        $this->armoryId = $armoryId;
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
     * @return GameClass
     */
    public function setName(string $name): GameClass
    {
        $this->name = $name;
        return $this;
    }
}

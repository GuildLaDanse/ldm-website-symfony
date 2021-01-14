<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\GameData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameData\GameRaceRepository")
 * @ORM\Table(name="GameRace", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class GameRace
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
     * @var GameFaction
     *
     * @ORM\ManyToOne(targetEntity="GameFaction")
     * @ORM\JoinColumn(name="faction", referencedColumnName="id", nullable=false)
     */
    protected GameFaction $faction;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return GameRace
     */
    public function setId(string $id): GameRace
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
     * @return GameRace
     */
    public function setArmoryId(int $armoryId): GameRace
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
     * @return GameRace
     */
    public function setName(string $name): GameRace
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return GameFaction
     */
    public function getFaction(): GameFaction
    {
        return $this->faction;
    }

    /**
     * @param GameFaction $faction
     * @return GameRace
     */
    public function setFaction(GameFaction $faction): GameRace
    {
        $this->faction = $faction;
        return $this;
    }
}

<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\CharacterOrigin;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\Character\Character;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterOrigin\TrackedByRepository")
 * @ORM\Table(name="TrackedBy", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 */
class TrackedBy
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
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected DateTime $fromTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected DateTime $endTime;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity=Character::class)
     * @ORM\JoinColumn(name="characterId", referencedColumnName="id", nullable=false)
     */
    protected Character $character;

    /**
     * @var CharacterSource
     *
     * @ORM\ManyToOne(targetEntity=CharacterSource::class)
     * @ORM\JoinColumn(name="characterSource", referencedColumnName="id", nullable=false)
     */
    protected CharacterSource $characterSource;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return TrackedBy
     */
    public function setId(string $id): TrackedBy
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getFromTime(): DateTime
    {
        return $this->fromTime;
    }

    /**
     * @param DateTime $fromTime
     * @return TrackedBy
     */
    public function setFromTime(DateTime $fromTime): TrackedBy
    {
        $this->fromTime = $fromTime;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getEndTime(): DateTime
    {
        return $this->endTime;
    }

    /**
     * @param DateTime $endTime
     * @return TrackedBy
     */
    public function setEndTime(DateTime $endTime): TrackedBy
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @return Character
     */
    public function getCharacter() : Character
    {
        return $this->character;
    }

    /**
     * @param Character $character
     * @return TrackedBy
     */
    public function setCharacter(Character $character) : TrackedBy
    {
        $this->character = $character;
        return $this;
    }

    /**
     * @return CharacterSource
     */
    public function getCharacterSource() : CharacterSource
    {
        return $this->characterSource;
    }

    /**
     * @param CharacterSource $characterSource
     * @return TrackedBy
     */
    public function setCharacterSource(CharacterSource $characterSource) : TrackedBy
    {
        $this->characterSource = $characterSource;
        return $this;
    }
}
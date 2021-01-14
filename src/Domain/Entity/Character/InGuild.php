<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\Character;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\GameData\Guild;

/**
 * @ORM\Entity
 * @ORM\Table(name="InGuild", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class InGuild
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
     * @var DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $endTime;

    /**
     * @var Guild
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\GameData\Guild")
     * @ORM\JoinColumn(name="guild", referencedColumnName="id", nullable=false)
     */
    protected Guild $guild;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character")
     * @ORM\JoinColumn(name="characterId", referencedColumnName="id", nullable=false)
     */
    protected Character $character;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return InGuild
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }

    /**
     * @param mixed $fromTime
     * @return InGuild
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $endTime
     * @return InGuild
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @return Guild
     */
    public function getGuild(): Guild
    {
        return $this->guild;
    }

    /**
     * @param Guild $guild
     * @return InGuild
     */
    public function setGuild(Guild $guild): InGuild
    {
        $this->guild = $guild;
        return $this;
    }

    /**
     * @return Character
     */
    public function getCharacter(): Character
    {
        return $this->character;
    }

    /**
     * @param Character $character
     * @return InGuild
     */
    public function setCharacter(Character $character): InGuild
    {
        $this->character = $character;
        return $this;
    }
}
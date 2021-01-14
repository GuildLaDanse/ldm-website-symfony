<?php declare(strict_types=1);

namespace App\Domain\Entity\Character;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\GameData\GameClass;
use App\Domain\Entity\GameData\GameRace;

/**
 * @ORM\Entity
 * @ORM\Table(name="GuildCharacterVersion")
 */
class CharacterVersion
{
    const REPOSITORY = 'LaDanseDomainBundle:CharacterVersion';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var Character
     *
     * @ORM\ManyToOne(targetEntity="Character", inversedBy="versions")
     * @ORM\JoinColumn(name="characterId", referencedColumnName="id", nullable=false)
     */
    protected Character $character;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", length=255, nullable=false)
     */
    protected DateTime $fromTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="datetime", length=255, nullable=true)
     */
    protected DateTime $endTime;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected int $level;

    /**
     * @var GameClass
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\GameData\GameClass")
     * @ORM\JoinColumn(name="gameClassId", referencedColumnName="id", nullable=false)
     */
    protected GameClass $gameClass;

    /**
     * @var GameRace
     *
     * @ORM\ManyToOne(targetEntity="\App\Entity\GameData\GameRace")
     * @ORM\JoinColumn(name="gameRaceId", referencedColumnName="id", nullable=false)
     */
    protected GameRace $gameRace;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fromTime
     *
     * @param DateTime $fromTime
     * @return CharacterVersion
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;

        return $this;
    }

    /**
     * Get fromTime
     *
     * @return DateTime
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }

    /**
     * Set endTime
     *
     * @param DateTime $endTime
     * @return CharacterVersion
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime
     *
     * @return DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return CharacterVersion
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }
    
    /**
     * Set character
     *
     * @param Character $character
     * @return CharacterVersion
     */
    public function setCharacter(Character $character)
    {
        $this->character = $character;

        return $this;
    }

    /**
     * Get character
     *
     * @return Character
     */
    public function getCharacter()
    {
        return $this->character;
    }

    /**
     * Set gameClass
     *
     * @param GameClass $gameClass
     * @return CharacterVersion
     */
    public function setGameClass(GameClass $gameClass)
    {
        $this->gameClass = $gameClass;

        return $this;
    }

    /**
     * Get gameClass
     *
     * @return GameClass
     */
    public function getGameClass()
    {
        return $this->gameClass;
    }

    /**
     * Set gameRace
     *
     * @param GameRace $gameRace
     * @return CharacterVersion
     */
    public function setGameRace(GameRace $gameRace)
    {
        $this->gameRace = $gameRace;

        return $this;
    }

    /**
     * Get gameRace
     *
     * @return GameRace
     */
    public function getGameRace()
    {
        return $this->gameRace;
    }

    /**
     * Return true if the given date is within the period of this version
     *
     * @param DateTime $onDateTime
     *
     * @return bool
     */
    public function isVersionActiveOn(DateTime $onDateTime)
    {
        if (($this->getFromTime() <= $onDateTime)
            &&
            (($this->getEndTime() > $onDateTime) || is_null($this->getEndTime())))
        {
            return true;
        }

        return false;
    }
}

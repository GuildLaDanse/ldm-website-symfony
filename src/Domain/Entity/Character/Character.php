<?php declare(strict_types=1);

namespace App\Domain\Entity\Character;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\GameData\Realm;

/**
 * @ORM\Entity
 * @ORM\Table(name="GuildCharacter")
 */
class Character
{
    const REPOSITORY = 'LaDanseDomainBundle:Character';

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $name;

    /**
     * @var Realm
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\GameData\Realm")
     * @ORM\JoinColumn(name="realm", referencedColumnName="id", nullable=false)
     */
    protected Realm $realm;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="CharacterVersion", mappedBy="character", cascade={"persist", "remove"})
     */
    protected ArrayCollection $versions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->versions = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Character
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Realm
     */
    public function getRealm(): Realm
    {
        return $this->realm;
    }

    /**
     * @param Realm $realm
     * @return Character
     */
    public function setRealm(Realm $realm): Character
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * Set fromTime
     *
     * @param DateTime $fromTime
     * @return Character
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
     * @return Character
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
     * Add versions
     *
     * @param CharacterVersion $versions
     * @return Character
     */
    public function addVersion(CharacterVersion $versions)
    {
        $this->versions[] = $versions;

        return $this;
    }

    /**
     * Remove versions
     *
     * @param CharacterVersion $versions
     */
    public function removeVersion(CharacterVersion $versions)
    {
        $this->versions->removeElement($versions);
    }

    /**
     * Get versions
     *
     * @return Collection
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Returns the CharacterVersion that is (was) active on the given date
     *
     * @param DateTime $onDateTime
     *
     * @return CharacterVersion|null
     */
    public function getVersionForDate(DateTime $onDateTime)
    {
        if (is_null($onDateTime))
        {
            return $this->versions[count($this->versions) - 1];
        }

        $activeVersion = null;

        /** @var $version CharacterVersion */
        foreach($this->versions as $version)
        {
            if ($version->isVersionActiveOn($onDateTime))
            {
                $activeVersion = $version;
            }
        }

        return $activeVersion;
    }
}

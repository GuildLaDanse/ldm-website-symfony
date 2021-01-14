<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\GameData;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GameData\GuildRepository")
 * @ORM\Table(name="Guild", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"}))
 */
class Guild
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
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected string $name;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $gameId;

    /**
     * @var Realm
     *
     * @ORM\ManyToOne(targetEntity="Realm")
     * @ORM\JoinColumn(name="realm", referencedColumnName="id", nullable=false)
     */
    protected Realm $realm;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Guild
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Guild
     */
    public function setName($name)
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
     * @return Guild
     */
    public function setGameId(?int $gameId): Guild
    {
        $this->gameId = $gameId;
        return $this;
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
     * @return Guild
     */
    public function setRealm(Realm $realm): Guild
    {
        $this->realm = $realm;
        return $this;
    }
}
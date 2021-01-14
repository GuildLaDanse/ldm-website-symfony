<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\CharacterOrigin;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="CharacterSource", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"GuildSync" = "GuildSync", "WoWProfileSync" = "WoWProfileSync"})
 */
abstract class CharacterSource
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
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return CharacterSource
     */
    public function setId(string $id): CharacterSource
    {
        $this->id = $id;
        return $this;
    }
}
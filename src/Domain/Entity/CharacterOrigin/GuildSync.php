<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Domain\Entity\CharacterOrigin;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Entity\GameData\Guild;

/**
 * @ORM\Entity
 * @ORM\Table(name="GuildSync", options={"collate":"utf8mb4_0900_ai_ci", "charset":"utf8mb4"})
 */
class GuildSync extends CharacterSource
{
    const REPOSITORY = 'LaDanseDomainBundle:CharacterOrigin\GuildSync';

    /**
     * @var Guild
     *
     * @ORM\ManyToOne(targetEntity=Guild::class)
     * @ORM\JoinColumn(name="guild", referencedColumnName="id", nullable=false)
     */
    protected Guild $guild;

    /**
     * @return Guild
     */
    public function getGuild(): Guild
    {
        return $this->guild;
    }

    /**
     * @param Guild $guild
     * @return GuildSync
     */
    public function setGuild(Guild $guild): GuildSync
    {
        $this->guild = $guild;
        return $this;
    }
}
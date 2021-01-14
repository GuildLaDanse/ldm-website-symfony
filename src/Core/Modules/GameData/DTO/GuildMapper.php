<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use App\Domain\Entity\GameData as GameDataEntity;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\Common\StringReference;

class GuildMapper
{
    /**
     * @param GameDataEntity\Guild $guild
     *
     * @return Guild
     */
    public static function mapSingle(GameDataEntity\Guild $guild) : Guild
    {
        $dtoGuild = new Guild();

        $dtoGuild->setId($guild->getId());
        $dtoGuild->setName($guild->getName());
        $dtoGuild->setGameId($guild->getGameId());
        $dtoGuild->setRealmReference(
            new StringReference($guild->getRealm()->getId())
        );

        return $dtoGuild;
    }

    /**
     * @param array $guilds
     *
     * @return array
     *
     * @throws MapperException
     */
    public static function mapArray(array $guilds) : array
    {
        $dtoGuildArray = [];

        foreach($guilds as $guild)
        {
            if (!($guild instanceof GameDataEntity\Guild))
            {
                throw new MapperException('Element in array is not of type Entity\GameData\Guild');
            }

            /** @var GameDataEntity\Guild $guild */
            $dtoGuildArray[] = GuildMapper::mapSingle($guild);
        }

        return $dtoGuildArray;
    }
}
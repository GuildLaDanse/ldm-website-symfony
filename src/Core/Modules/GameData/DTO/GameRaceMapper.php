<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use App\Domain\Entity\GameData as GameDataEntity;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\Common\StringReference;

class GameRaceMapper
{
    /**
     * @param GameDataEntity\GameRace $gameRace
     *
     * @return GameRace
     */
    public static function mapSingle(GameDataEntity\GameRace $gameRace) : GameRace
    {
        $dtoGameRace = new GameRace();

        $dtoGameRace->setId($gameRace->getId());
        $dtoGameRace->setArmoryId($gameRace->getArmoryId());
        $dtoGameRace->setName($gameRace->getName());
        $dtoGameRace->setGameFactionReference(
            new StringReference($gameRace->getFaction()->getId())
        );

        return $dtoGameRace;
    }

    /**
     * @param array $gameRaces
     *
     * @return array
     *
     * @throws MapperException
     */
    public static function mapArray(array $gameRaces) : array
    {
        $dtoGameRaceArray = [];

        foreach($gameRaces as $gameRace)
        {
            if (!($gameRace instanceof GameDataEntity\GameRace))
            {
                throw new MapperException('Element in array is not of type Entity\GameData\GameRace');
            }

            /** @var GameDataEntity\GameRace $gameRace */
            $dtoGameRaceArray[] = GameRaceMapper::mapSingle($gameRace);
        }

        return $dtoGameRaceArray;
    }
}
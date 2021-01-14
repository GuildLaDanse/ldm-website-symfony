<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use App\Domain\Entity\GameData as GameDataEntity;
use App\Core\Modules\Common\MapperException;

class GameClassMapper
{
    /**
     * @param GameDataEntity\GameClass $gameClass
     *
     * @return GameClass
     */
    public static function mapSingle(GameDataEntity\GameClass $gameClass) : GameClass
    {
        $dtoGameClass = new GameClass();

        $dtoGameClass->setId($gameClass->getId());
        $dtoGameClass->setArmoryId($gameClass->getArmoryId());
        $dtoGameClass->setName($gameClass->getName());

        return $dtoGameClass;
    }

    /**
     * @param array $gameClasss
     *
     * @return array
     *
     * @throws MapperException
     */
    public static function mapArray(array $gameClasss) : array
    {
        $dtoGameClassArray = [];

        foreach($gameClasss as $gameClass)
        {
            if (!($gameClass instanceof GameDataEntity\GameClass))
            {
                throw new MapperException('Element in array is not of type Entity\GameData\GameClass');
            }

            /** @var GameDataEntity\GameClass $gameClass */
            $dtoGameClassArray[] = GameClassMapper::mapSingle($gameClass);
        }

        return $dtoGameClassArray;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\DTO;

use App\Domain\Entity\GameData as GameDataEntity;
use App\Core\Modules\Common\MapperException;

class RealmMapper
{
    /**
     * @param GameDataEntity\Realm $realm
     *
     * @return Realm
     */
    public static function mapSingle(GameDataEntity\Realm $realm) : Realm
    {
        $dtoRealm = new Realm();

        $dtoRealm->setId($realm->getId());
        $dtoRealm->setName($realm->getName());
        $dtoRealm->setGameId($realm->getGameId());

        return $dtoRealm;
    }

    /**
     * @param array $realms
     *
     * @return array
     *
     * @throws MapperException
     */
    public static function mapArray(array $realms) : array
    {
        $dtoRealmArray = [];

        foreach($realms as $realm)
        {
            if (!($realm instanceof GameDataEntity\Realm))
            {
                throw new MapperException('Element in array is not of type Entity\GameData\Realm');
            }

            /** @var GameDataEntity\Realm $realm */
            $dtoRealmArray[] = RealmMapper::mapSingle($realm);
        }

        return $dtoRealmArray;
    }
}
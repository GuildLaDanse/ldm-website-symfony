<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\DTO;

use App\Domain\Entity\Character as CharacterEntity;
use App\Core\Modules\Character\Query\CharacterHydrator;
use App\Core\Modules\Common\AccountReference;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\Common\StringReference;

class CharacterMapper
{
    /**
     * @param CharacterEntity\CharacterVersion $characterVersion
     * @param CharacterHydrator $characterHydrator
     *
     * @return Character
     *
     * @internal param Entity\Claim $claim
     *
     */
    public static function mapSingle(
        CharacterEntity\CharacterVersion $characterVersion,
        CharacterHydrator $characterHydrator) : Character
    {
        $dto = new Character();

        $dto->setId($characterVersion->getCharacter()->getId());
        $dto->setName($characterVersion->getCharacter()->getName());
        $dto->setLevel($characterVersion->getLevel());

        $dto->setRealmReference(
            new StringReference($characterVersion->getCharacter()->getRealm()->getId())
        );

        $dto->setGameRaceReference(
            new StringReference($characterVersion->getGameRace()->getId())
        );

        $dto->setGameClassReference(
            new StringReference($characterVersion->getGameClass()->getId())
        );

        $inGuild = $characterHydrator->getGuild($characterVersion->getCharacter()->getId());

        if ($inGuild !== null)
        {
            $dto->setGuildReference(
                new StringReference($inGuild->getGuild()->getId())
            );
        }

        if ($characterHydrator->hasBeenClaimed($characterVersion->getCharacter()->getId()))
        {
            $claim = $characterHydrator->getClaimVersion($characterVersion->getCharacter()->getId());

            $claimDto = new Claim();
            $claimDto
                ->setComment($claim->getComment())
                ->setAccountReference(
                    new AccountReference(
                        $claim->getClaim()->getAccount()->getId(),
                        $claim->getClaim()->getAccount()->getDisplayName()
                    )
                )
                ->setRaider($claim->isRaider())
                ->setRoles($characterHydrator->getClaimedRoles($characterVersion->getCharacter()->getId()));

            $dto->setClaim($claimDto);
        }

        return $dto;
    }

    /**
     * @param array $characterVersions
     * @param CharacterHydrator $characterHydrator
     *
     * @return array
     *
     * @throws MapperException
     *
     */
    public static function mapArray(array $characterVersions, CharacterHydrator $characterHydrator) : array
    {
        $dtoArray = [];

        foreach($characterVersions as $characterVersion)
        {
            if (!($characterVersion instanceof CharacterEntity\CharacterVersion))
            {
                throw new MapperException('Element in array is not of type Entity\CharacterVersion');
            }

            /** @var CharacterEntity\CharacterVersion $characterVersion */
            $dtoArray[] = CharacterMapper::mapSingle(
                $characterVersion,
                $characterHydrator);
        }

        return $dtoArray;
    }
}
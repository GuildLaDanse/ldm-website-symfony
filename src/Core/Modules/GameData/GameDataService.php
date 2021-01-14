<?php /** @noinspection PhpDocRedundantThrowsInspection */
declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData;

use App\Infrastructure\Messenger\CommandBusTrait;
use App\Infrastructure\Messenger\QueryBusTrait;
use App\Infrastructure\Modules\InvalidInputException;
use App\Core\Modules\GameData\DTO as GameDataDTO;
use App\Core\Modules\GameData\Query\GetAllGameRaces\GetAllGameRacesQuery;
use App\Core\Modules\GameData\Command\PostGuild\PostGuildCommand;
use App\Core\Modules\GameData\Command\PostRealm\PostRealmCommand;
use App\Core\Modules\GameData\Query\GetAllGameClasses\GetAllGameClassesQuery;
use App\Core\Modules\GameData\Query\GetAllGameFactions\GetAllGameFactionsQuery;
use App\Core\Modules\GameData\Query\GetAllGuilds\GetAllGuildsQuery;
use App\Core\Modules\GameData\Query\GetAllRealms\GetAllRealmsQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class GameDataService
{
    use CommandBusTrait;
    use QueryBusTrait;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     * @param MessageBusInterface $commandBus
     * @param MessageBusInterface $queryBus
     */
    public function __construct(
        LoggerInterface $logger,
        MessageBusInterface $commandBus,
        MessageBusInterface $queryBus)
    {
        $this->logger = $logger;
        $this->_commandBus = $commandBus;
        $this->_queryBus = $queryBus;
    }

    /**
     * @return GameDataDTO\GameRace[]
     * 
     * @throws Throwable
     */
    public function getAllGameRaces(): array
    {
        $query = new GetAllGameRacesQuery();

        return $this->dispatchQuery($query);
    }

    /**
     * @return GameDataDTO\GameClass[]
     */
    public function getAllGameClasses(): array
    {
        $query = new GetAllGameClassesQuery();

        return $this->dispatchQuery($query);
    }

    /**
     * @return GameDataDTO\GameFaction[]
     */
    public function getAllGameFactions() : array
    {
        $query = new GetAllGameFactionsQuery();

        return $this->dispatchQuery($query);
    }

    /**
     * @return GameDataDTO\Guild[]
     */
    public function getAllGuilds() : array
    {
        $query = new GetAllGuildsQuery();

        return $this->dispatchQuery($query);
    }

    /**
     * @param GameDataDTO\PatchGuild $patchGuild
     *
     * @return GameDataDTO\Guild
     *
     * @throws GuildAlreadyExistsException
     * @throws RealmDoesNotExistException
     * @throws InvalidInputException
     */
    public function postGuild(GameDataDTO\PatchGuild $patchGuild) : GameDataDTO\Guild
    {
        $command = new PostGuildCommand($patchGuild);

        return $this->dispatchCommand($command);
    }

    /**
     * @param string $guildId
     * @param GameDataDTO\PatchGuild $patchGuild
     */
    public function patchGuild(string $guildId, GameDataDTO\PatchGuild $patchGuild): void
    {
        $this->logger->warning(
            "patchGuild not implemented",
            [
                "guildId" => $guildId,
                "patchGuild" => $patchGuild
            ]);

        throw new NotImplementedException("Not yet implemented");
    }

    /**
     * @param string $guildId
     */
    public function deleteGuild(string $guildId): void
    {
        $this->logger->warning(
            "deleteGuild not implemented",
            [
                "guildId" => $guildId
            ]);

        throw new NotImplementedException("deleteGuild is not yet implemented");
    }

    /**
     * @return GameDataDTO\Realm[]
     */
    public function getAllRealms() : array
    {
        $query = new GetAllRealmsQuery();

        return $this->dispatchQuery($query);
    }

    /**
     * @param GameDataDTO\PatchRealm $patchRealm
     *
     * @return GameDataDTO\Realm
     *
     * @throws InvalidInputException
     * @throws RealmAlreadyExistsException
     */
    public function postRealm(GameDataDTO\PatchRealm $patchRealm) : GameDataDTO\Realm
    {
        $command = new PostRealmCommand($patchRealm);

        return $this->dispatchCommand($command);
    }

    /**
     * @param string $realmId
     * @param DTO\PatchRealm $patchRealm
     */
    public function patchRealm(string $realmId, GameDataDTO\PatchRealm $patchRealm): void
    {
        $this->logger->warning(
            "patchRealm not implemented",
            [
                "realmId" => $realmId,
                "patchRealm" => $patchRealm
            ]);

        throw new NotImplementedException("patchRealm is not yet implemented");
    }

    /**
     * @param string $realmId
     */
    public function deleteRealm(string $realmId): void
    {
        $this->logger->warning(
            "deleteRealm not implemented",
            [
                "realmId" => $realmId
            ]);

        throw new NotImplementedException("deleteRealm is not yet implemented");
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character;

use App\Infrastructure\Messenger\CommandBusTrait;
use App\Infrastructure\Messenger\QueryBusTrait;
use App\Core\Modules\Character\Command\CharacterSessionImpl;
use App\Core\Modules\Character\Command\CreateGuildSyncSession\CreateGuildSyncSessionCommand;
use App\Core\Modules\Character\Command\DeleteClaim\DeleteClaimCommand;
use App\Core\Modules\Character\Command\PatchCharacter\PatchCharacterCommand;
use App\Core\Modules\Character\Command\PostClaim\PostClaimCommand;
use App\Core\Modules\Character\Command\PutClaim\PutClaimCommand;
use App\Core\Modules\Character\Command\TrackCharacter\TrackCharacterCommand;
use App\Core\Modules\Character\Command\UntrackCharacter\UntrackCharacterCommand;
use App\Core\Modules\Character\DTO as CharacterDTO;
use App\Core\Modules\Character\DTO\PatchCharacter;
use App\Core\Modules\Character\DTO\PatchClaim;
use App\Core\Modules\Character\DTO\SearchCriteria;
use App\Core\Modules\Character\Query\CharactersByCriteria\CharactersByCriteriaQuery;
use App\Core\Modules\Character\Query\CharactersClaimedByAccount\CharactersClaimedByAccountQuery;
use App\Core\Modules\Character\Query\GetAllCharactersInGuild\GetAllCharactersInGuildQuery;
use App\Core\Modules\Character\Query\GetAllClaimedCharacters\GetAllClaimedCharactersQuery;
use App\Core\Modules\Character\Query\GetCharacterById\GetCharacterByIdQuery;
use App\Core\Modules\Common\StringReference;
use DateTime;
use Exception;
use RuntimeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class CharacterService
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
     * @param int $characterId
     * @param DateTime|null $onDateTime
     *
     * @return CharacterDTO\Character|null
     *
     * @throws Exception
     */
    public function getCharacterById(int $characterId, DateTime $onDateTime = null): ?CharacterDTO\Character
    {
        if ($onDateTime === null)
        {
            $onDateTime = new DateTime();
        }

        $query = new GetCharacterByIdQuery($characterId, $onDateTime);

        return $this->dispatchQuery($query);
    }

    /**
     * Returns an array of all characters who were in the guild on $onDateTime
     * Properties are taken as valid on $onDateTime
     *
     * @param StringReference $guildReference
     * @param DateTime $onDateTime if left null, the current date and time is used
     *
     * @return array
     *
     * @throws Exception
     */
    public function getAllCharactersInGuild(StringReference $guildReference, DateTime $onDateTime = null): array
    {
        if ($onDateTime === null)
        {
            $onDateTime = new DateTime();
        }

        $query = new GetAllCharactersInGuildQuery($guildReference, $onDateTime);

        return $this->dispatchQuery($query);
    }

    /**
     * @param int $accountId
     * @param DateTime $onDateTime
     *
     * @return array
     *
     * @throws Exception
     */
    public function getCharactersClaimedByAccount(int $accountId, DateTime $onDateTime = null) : array
    {
        if ($onDateTime === null)
        {
            $onDateTime = new DateTime();
        }

        $query = new CharactersClaimedByAccountQuery($accountId, $onDateTime);

        return $this->dispatchQuery($query);
    }

    /**
     * @param DateTime $onDateTime
     *
     * @return array
     *
     * @throws Exception
     */
    public function getAllClaimedCharacters(DateTime $onDateTime = null) : array
    {
        if ($onDateTime === null)
        {
            $onDateTime = new DateTime();
        }

        $query = new GetAllClaimedCharactersQuery($onDateTime);

        return $this->dispatchQuery($query);
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @param DateTime|null $onDateTime
     *
     * @return array
     *
     * @throws Exception
     */
    public function getCharactersByCriteria(SearchCriteria $searchCriteria, DateTime $onDateTime = null) : array
    {
        if ($onDateTime === null)
        {
            $onDateTime = new DateTime();
        }

        $query = new CharactersByCriteriaQuery($searchCriteria,$onDateTime);

        return $this->dispatchQuery($query);
    }

    /**
     * @param CharacterSession $characterSession
     * @param PatchCharacter $patchCharacter
     *
     * @return CharacterDTO\Character
     *
     * @throws Exception
     */
    public function trackCharacter(CharacterSession $characterSession, PatchCharacter $patchCharacter): CharacterDTO\Character
    {
        $command = new TrackCharacterCommand($characterSession, $patchCharacter);

        $characterId = $this->dispatchCommand($command);

        return $this->getCharacterById($characterId);
    }

    /**
     * @param CharacterSession $characterSession
     * @param int $characterId
     */
    public function untrackCharacter(CharacterSession $characterSession, int $characterId)
    {
        $command = new UntrackCharacterCommand($characterSession, $characterId);

        $this->dispatchCommand($command);
    }

    /**
     * @param CharacterSession $characterSession
     * @param int $characterId
     * @param PatchCharacter $patchCharacter
     *
     * @return CharacterDTO\Character
     *
     * @throws Exception
     */
    public function patchCharacter(
        CharacterSession $characterSession,
        int $characterId,
        PatchCharacter $patchCharacter): CharacterDTO\Character
    {
        $command = new PatchCharacterCommand($characterId, $patchCharacter, $characterSession);

        $this->dispatchCommand($command);

        return $this->getCharacterById($characterId);
    }

    /**
     * @param int $characterId
     * @param int $accountId
     * @param PatchClaim $patchClaim
     *
     * @return CharacterDTO\Character
     *
     * @throws Exception
     */
    public function postClaim(int $characterId, int $accountId, PatchClaim $patchClaim): CharacterDTO\Character
    {
        $command = new PostClaimCommand($characterId, $accountId, $patchClaim);

        $this->dispatchCommand($command);

        return $this->getCharacterById($characterId);
    }

    /**
     * @param int $characterId
     * @param PatchClaim $patchClaim
     *
     * @return CharacterDTO\Character
     *
     * @throws Exception
     */
    public function putClaim(int $characterId, PatchClaim $patchClaim): CharacterDTO\Character
    {
        $command = new PutClaimCommand($characterId, $patchClaim);

        $this->dispatchCommand($command);

        return $this->getCharacterById($characterId);
    }

    /**
     * @param int $characterId
     *
     * @return CharacterDto\Character
     *
     * @throws Exception
     */
    public function deleteClaim(int $characterId): CharacterDto\Character
    {
        $command = new DeleteClaimCommand($characterId);

        $this->dispatchCommand($command);

        return $this->getCharacterById($characterId);
    }

    /**
     * @param StringReference $guildId
     *
     * @return CharacterSession
     */
    public function createGuildSyncSession(StringReference $guildId) : CharacterSession
    {
        $command = new CreateGuildSyncSessionCommand($guildId);

        return $this->dispatchCommand($command);
    }

    /**
     * @param CharacterSession $characterSession
     *
     * @return CharacterService
     *
     * @throws Exception
     */
    public function endCharacterSession(CharacterSession $characterSession) : CharacterService
    {
        if (!($characterSession instanceof CharacterSessionImpl))
        {
            throw new RuntimeException("Unknown implementation for CharacterSession");
        }

        /** @var CharacterSessionImpl $characterSessionImpl */
        $characterSessionImpl = $characterSession;

        $characterSessionImpl->endSession();

        return $this;
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\CreateGuildSyncSession;

use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Modules\ServiceException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Character\CharacterSession;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\GameData as GameDataEntity;
use App\Domain\Entity\CharacterOrigin as CharacterOriginEntity;

class CreateGuildSyncSessionCommandHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var CharacterSession
     */
    private CharacterSession $characterSession;

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * CreateGuildSyncSessionCommandHandler constructor.
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param CharacterSession $characterSession
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        CharacterSession $characterSession,
        ManagerRegistry $doctrine)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->characterSession = $characterSession;
        $this->doctrine = $doctrine;
    }

    /**
     * @param CreateGuildSyncSessionCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(CreateGuildSyncSessionCommand $command): void
    {
        if ($command->getGuildId() === null)
        {
            throw new InvalidInputException("Given guild id was null");
        }
    }

    /**
     * @param CreateGuildSyncSessionCommand $command
     *
     * @return CharacterSession
     *
     * @throws InvalidInputException
     * @throws ServiceException
     */
    public function __invoke(CreateGuildSyncSessionCommand $command)
    {
        $this->validateInput($command);

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('gs')
            ->from(CharacterOriginEntity\GuildSync::class, 'gs')
            ->where('gs.guild = ?1')
            ->setParameter(1, $em->getReference(GameDataEntity\Guild::class, $command->getGuildId()));

        /* @var $query $dbQuery */
        $dbQuery = $qb->getQuery();

        $sources = $dbQuery->getResult();

        /** @var CharacterOriginEntity\GuildSync $guildSync */
        $guildSync = null;

        if (count($sources) == 0)
        {
            // there is no GuildSync yet for this guild, create it on the fly

            $guildSync = new CharacterOriginEntity\GuildSync();
            $guildSync->setGuild($em->getReference(GameDataEntity\Guild::class, $command->getGuildId()));

            $em->persist($guildSync);
        }
        else if (count($sources) == 1)
        {
            $guildSync = $sources[0];
        }
        else
        {
            throw new ServiceException(
                sprintf(
                    "Found multiple GuildSync instances for the guild %s",
                    $command->getGuildId()
                ),
                500
            );
        }

        $this->characterSession->startSession($guildSync);

        return $this->characterSession;
    }
}
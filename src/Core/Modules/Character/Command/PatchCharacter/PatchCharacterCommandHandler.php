<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\PatchCharacter;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Character\Command\CharacterSessionImpl;
use App\Repository\CharacterOrigin\TrackedByRepository;
use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\CharacterOrigin as CharacterOriginEntity;
use App\Domain\Entity\Character as CharacterEntity;
use App\Domain\Entity\GameData as GameDataEntity;

class PatchCharacterCommandHandler implements MessageHandlerInterface
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
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;

    /**
     * PatchCharacterCommandHandler constructor.
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $doctrine
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        AuthenticationService $authenticationService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param PatchCharacterCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PatchCharacterCommand $command): void
    {
        if ($command->getPatchCharacter() === null)
        {
            throw new InvalidInputException("characterSession or patchCharacter can't be null");
        }

        if (!($command->getCharacterSession() instanceof CharacterSessionImpl))
        {
            throw new InvalidInputException("Unrecognized CharacterSession implementation");
        }
    }

    /**
     * @param PatchCharacterCommand $command
     *
     * @throws InvalidInputException
     * @throws ServiceException
     * @throws ORMException
     */
    public function __invoke(PatchCharacterCommand $command): void
    {
        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        // create a shared $fromTime since we might need it often below
        $fromTime = new DateTime();

        $em = $this->doctrine->getManager();

        /** @var CharacterSessionImpl $characterSessionImpl */
        $characterSessionImpl = $command->getCharacterSession();

        // verify if the characterSource actually tracks this character

        /** @var TrackedByRepository $trackedByRepo */
        $trackedByRepo = $this->doctrine->getRepository(CharacterOriginEntity\TrackedBy::class);

        $trackedBys = $trackedByRepo->findTrackedBysForCharacter(
            $characterSessionImpl->getCharacterSource(),
            $command->getCharacterId());

        if (count($trackedBys) == 0)
        {
            throw new ServiceException(
                sprintf(
                    "The character %s is not being tracked by current characterSource, use POST to create it",
                    $command->getCharacterId()
                ),
                400
            );
        }

        /**
         * check if character already exists (name + realm as combined unique key)
         *  if it already exists
         *      verify if the characterSource isn't already tracking this character
         *          if it is already being tracked, throw exception as it should do a PUT and not a POST
         *          if it is not being tracked, add tracker
         *      define a delta and update character
         *  if it does not exist
         *      create character and add tracker
         */

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('charVersion', 'char', 'gameRace', 'gameClass', 'realm')
            ->from(CharacterEntity\CharacterVersion::class, 'charVersion')
            ->join('charVersion.character', 'char')
            ->join('charVersion.gameRace', 'gameRace')
            ->join('charVersion.gameClass', 'gameClass')
            ->join('char.realm', 'realm')
            ->where('char.id = ?1')
            ->andWhere('char.fromTime IS NOT NULL')
            ->andWhere('char.endTime IS NULL')
            ->andWhere('charVersion.fromTime IS NOT NULL')
            ->andWhere('charVersion.endTime IS NULL')
            ->setParameter(1, $command->getCharacterId());

        /* @var $query Query */
        $query = $qb->getQuery();

        $characterVersions = $query->getResult();

        if (count($characterVersions) == 0)
        {
            // character does not yet exist, should never happen

            throw new ServiceException(
                sprintf(
                    "An active character with id %s was not found, use POST to create it",
                    $command->getCharacterId()
                ),
                400
            );
        }
        elseif(count($characterVersions) != 1)
        {
            // this should never happen, we cannot have two characters with the same name on the same realm

            throw new ServiceException(
                sprintf(
                    "Multiple characters with the id %s found",
                    $command->getCharacterId()
                ),
                500
            );
        }

        /** @var CharacterEntity\CharacterVersion $currentCharacterVersion */
        $currentCharacterVersion = $characterVersions[0];

        /*
         * Verify that there is no attempt to change immutable fields
         */

        if ($currentCharacterVersion->getCharacter()->getName() != $command->getPatchCharacter()->getName()
            ||
            $currentCharacterVersion->getCharacter()->getRealm()->getId()
                != $command->getPatchCharacter()->getRealmReference()->getId())
        {
            throw new ServiceException(
                sprintf(
                    "Attempt to change name or realm on character with id %s",
                    $command->getCharacterId()
                ),
                400
            );
        }

        /*
         * Fields to compare:
         *
         * - in CharacterVersion
         *      - level
         *      - gameClass
         *      - gameRace
         * - in Character
         *      - Guild
         */

        // check if we need to make a new CharacterVersion

        if ($currentCharacterVersion->getLevel() != $command->getPatchCharacter()->getLevel()
            ||
            $currentCharacterVersion->getGameRace()->getId() != $command->getPatchCharacter()->getGameRaceReference()->getId()
            ||
            $currentCharacterVersion->getGameClass()->getId() != $command->getPatchCharacter()->getGameClassReference()->getId())
        {
            $currentCharacterVersion->setEndTime($fromTime);

            $newCharacterVersion = new CharacterEntity\CharacterVersion();

            $newCharacterVersion->setCharacter($currentCharacterVersion->getCharacter());
            $newCharacterVersion->setLevel($command->getPatchCharacter()->getLevel());
            $newCharacterVersion->setFromTime($fromTime);
            $newCharacterVersion->setGameRace(
                $em->getReference(
                    GameDataEntity\GameRace::class,
                    $command->getPatchCharacter()->getGameRaceReference()->getId()
                )
            );
            $newCharacterVersion->setGameClass(
                $em->getReference(
                    GameDataEntity\GameClass::class,
                    $command->getPatchCharacter()->getGameClassReference()->getId()
                )
            );

            $em->persist($newCharacterVersion);
            $em->flush();
        }

        // check if we need to change the guild association

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('inGuild', 'guild')
            ->from(CharacterEntity\InGuild::class, 'inGuild')
            ->join('inGuild.guild', 'guild')
            ->where('inGuild.character = ?1')
            ->andWhere('inGuild.fromTime IS NOT NULL')
            ->andWhere('inGuild.endTime IS NULL')
            ->setParameter(1, $currentCharacterVersion->getCharacter());

        /* @var $query Query */
        $query = $qb->getQuery();

        $inGuilds = $query->getResult();

        if (count($inGuilds) == 0)
        {
            // the character is currently not in a guild, verify if it should be

            if ($command->getPatchCharacter()->getGuildReference() !== null)
            {
                $newInGuild = new CharacterEntity\InGuild();
                $newInGuild->setFromTime($fromTime);
                $newInGuild->setCharacter($currentCharacterVersion->getCharacter());
                $newInGuild->setGuild(
                    $em->getReference(
                        GameDataEntity\Guild::class,
                        $command->getPatchCharacter()->getGuildReference()->getId()
                    )
                );

                $em->persist($newInGuild);
                $em->flush();
            }
        }
        elseif (count($inGuilds) == 1)
        {
            // the character is currently in a guild, verify if it needs to change

            /** @var CharacterEntity\InGuild $inGuild */
            $inGuild = $inGuilds[0];

            if ($command->getPatchCharacter()->getGuildReference() === null)
            {
                $inGuild->setEndTime($fromTime);
                $em->flush();
            }
            else if ($inGuild->getGuild()->getId() != $command->getPatchCharacter()->getGuildReference()->getId())
            {
                $inGuild->setEndTime($fromTime);

                $newInGuild = new CharacterEntity\InGuild();
                $newInGuild->setFromTime($fromTime);
                $newInGuild->setCharacter($currentCharacterVersion->getCharacter());
                $newInGuild->setGuild(
                    $em->getReference(
                        GameDataEntity\Guild::class,
                        $command->getPatchCharacter()->getGuildReference()->getId()
                    )
                );

                $em->persist($newInGuild);
                $em->flush();
            }
        }
        else
        {
            // according to the database the character is in two guilds at the same time, this is an error

            throw new ServiceException(
                sprintf(
                    "Two active inGuilds for character %s ",
                    $command->getCharacterId()
                ),
                500
            );
        }

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::CHARACTER_UPDATE,
                $$account,
                [
                    'accountId'      => $account !== null ? $account->getId() : null,
                    'characterId'    => $command->getCharacterId(),
                    'patchCharacter' => ActivityEvent::annotatedToSimpleObject($command->getPatchCharacter())
                ]
            )
        );
    }
}
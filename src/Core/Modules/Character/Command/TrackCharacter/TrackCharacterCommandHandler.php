<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\TrackCharacter;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\Character\CharacterService;
use App\Core\Modules\Character\Command\CharacterSessionImpl;
use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;
use App\Domain\Entity\GameData as GameDataEntity;
use App\Domain\Entity\CharacterOrigin as CharacterOriginEntity;

class TrackCharacterCommandHandler implements MessageHandlerInterface
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
     * @var CharacterService
     */
    private CharacterService $characterService;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;

    /**
     * @var AuthorizationService
     */
    private AuthorizationService $authzService;

    /**
     * PostClaimCommandHandler constructor.
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $doctrine
     * @param CharacterService $characterService
     * @param AuthenticationService $authenticationService
     * @param AuthorizationService $authzService
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        CharacterService $characterService,
        AuthenticationService $authenticationService,
        AuthorizationService $authzService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->characterService = $characterService;
        $this->authenticationService = $authenticationService;
        $this->authzService = $authzService;
    }

    /**
     * @param TrackCharacterCommand $command
     *
     * @throws InvalidInputException
     */
    private function validateInput(TrackCharacterCommand $command): void
    {
        if ($command->getPatchCharacter() === null || $command->getCharacterSession() === null)
        {
            throw new InvalidInputException("characterSession or patchCharacter can't be null");
        }

        if (!($command->getCharacterSession() instanceof CharacterSessionImpl))
        {
            throw new InvalidInputException("Unrecognized CharacterSession implementation");
        }
    }

    /**
     * @param TrackCharacterCommand $command
     *
     * @return mixed
     *
     * @throws InvalidInputException
     * @throws ServiceException
     * @throws Exception
     */
    public function __invoke(TrackCharacterCommand $command)
    {
        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        /** @var CharacterSessionImpl $characterSessionImpl */
        $characterSessionImpl = $command->getCharacterSession();

        /**
         * check if character already exists (name + realm as combined unique key)
         *  if it already exists
         *      verify if the characterSource isn't already tracking this character
         *          if it is already being tracked, throw exception as it should be a PUT and not a POST
         *          if it is not being tracked, add tracker and update
         *  if it does not exist
         *      create character and add tracker
         */

        // create a shared $fromTime since we will need it often below
        $fromTime = new DateTime();

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('c')
            ->from(CharacterEntity\Character::class, 'c')
            ->join('c.realm', 'realm')
            ->where('c.name = collate(?1, utf8_bin)')
            ->andWhere('realm.id = ?2')
            ->andWhere('c.fromTime IS NOT NULL')
            ->andWhere('c.endTime IS NULL')
            ->setParameter(1, $command->getPatchCharacter()->getName())
            ->setParameter(
                2,
                $em->getReference(GameDataEntity\Realm::class, $command->getPatchCharacter()->getRealmReference()->getId())
            );

        /* @var $query Query */
        $query = $qb->getQuery();

        $characters = $query->getResult();

        if (count($characters) == 0)
        {
            // character does not yet exist, so we have to create it and create a tracker for the character source

            $character = new CharacterEntity\Character();
            $character->setName($command->getPatchCharacter()->getName());
            $character->setFromTime($fromTime);
            $character->setRealm(
                $em->getReference(
                    GameDataEntity\Realm::class,
                    $command->getPatchCharacter()->getRealmReference()->getId()
                )
            );

            $em->persist($character);

            $characterVersion = new CharacterEntity\CharacterVersion();
            $characterVersion->setCharacter($character);
            $characterVersion->setLevel($command->getPatchCharacter()->getLevel());
            $characterVersion->setFromTime($fromTime);
            $characterVersion->setGameClass(
                $em->getReference(
                    GameDataEntity\GameClass::class,
                    $command->getPatchCharacter()->getGameClassReference()->getId()
                )
            );
            $characterVersion->setGameRace(
                $em->getReference(
                    GameDataEntity\GameRace::class,
                    $command->getPatchCharacter()->getGameRaceReference()->getId()
                )
            );

            $em->persist($characterVersion);

            $trackedBy = new CharacterOriginEntity\TrackedBy();
            $trackedBy->setCharacter($character);
            $trackedBy->setFromTime($fromTime);
            $trackedBy->setCharacterSource($characterSessionImpl->getCharacterSource());

            $em->persist($trackedBy);

            if ($command->getPatchCharacter()->getGuildReference() !== null)
            {
                $inGuild = new CharacterEntity\InGuild();
                $inGuild->setCharacter($character);
                $inGuild->setFromTime($fromTime);
                $inGuild->setGuild(
                    $em->getReference(
                        GameDataEntity\Guild::class,
                        $command->getPatchCharacter()->getGuildReference()->getId()
                    )
                );

                $em->persist($inGuild);
            }

            $em->flush();

            $this->eventDispatcher->dispatch(
                new ActivityEvent(
                    ActivityType::CHARACTER_CREATE,
                    $account,
                    [
                        'accountId'      => $account !== null ? $account->getId() : null,
                        'patchCharacter' => ActivityEvent::annotatedToSimpleObject($command->getPatchCharacter())
                    ]
                )
            );

            return $character->getId();
        }
        elseif(count($characters) == 1)
        {
            // character already exists, verify that this character source is not already tracking it

            /** @var CharacterEntity\Character $character */
            $character = $characters[0];

            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();

            $qb->select('t')
                ->from(CharacterOriginEntity\TrackedBy::class, 't')
                ->join('t.characterSource', 's')
                ->where('t.character = ?1')
                ->andWhere('s = ?2')
                ->andWhere('t.fromTime IS NOT NULL')
                ->andWhere('t.endTime IS NULL')
                ->setParameter(1, $character)
                ->setParameter(2, $characterSessionImpl->getCharacterSource());

            /* @var Query $query */
            $query = $qb->getQuery();

            $trackers = $query->getResult();

            if (count($trackers) != 0)
            {
                throw new ServiceException(
                    sprintf(
                        "Character %s is already actively tracked by characterSource %s",
                        $character->getId(),
                        $characterSessionImpl->getCharacterSource()->getId()
                    ),
                    400
                );
            }

            $trackedBy = new CharacterOriginEntity\TrackedBy();
            $trackedBy->setCharacter($character);
            $trackedBy->setFromTime($fromTime);
            $trackedBy->setCharacterSource($characterSessionImpl->getCharacterSource());

            $em->persist($trackedBy);

            $this->eventDispatcher->dispatch(
                new ActivityEvent(
                    ActivityType::CHARACTER_TRACK,
                    $account,
                    [
                        'accountId'      => $account !== null ? $account->getId() : null,
                        'characterId'    => $character->getId(),
                        'patchCharacter' => ActivityEvent::annotatedToSimpleObject($command->getPatchCharacter())
                    ]
                )
            );

            $this->characterService->patchCharacter(
                $command->getCharacterSession(),
                $character->getId(),
                $command->getPatchCharacter()
            );

            return $character->getId();
        }
        else
        {
            // this should never happen, we cannot have two active characters with the same name on the same realm

            throw new ServiceException(
                sprintf(
                    "Two active characters with the name %s on the realm %s found",
                    $command->getPatchCharacter()->getName(),
                    $command->getPatchCharacter()->getRealmReference()->getId()
                ),
                400
            );
        }
    }
}
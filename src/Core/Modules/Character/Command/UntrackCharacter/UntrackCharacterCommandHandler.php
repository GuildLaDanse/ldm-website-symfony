<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\UntrackCharacter;

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
use App\Repository\CharacterOrigin\TrackedByRepository;
use DateTime;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;
use App\Domain\Entity\CharacterOrigin as CharacterOriginEntity;

class UntrackCharacterCommandHandler implements MessageHandlerInterface
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
     * @param UntrackCharacterCommand $command
     *
     * @throws InvalidInputException
     */
    private function validateInput(UntrackCharacterCommand $command): void
    {
        if (!($command->getCharacterSession() instanceof CharacterSessionImpl))
        {
            throw new InvalidInputException("Unrecognized CharacterSession implementation");
        }
    }

    /**
     * @param UntrackCharacterCommand $command
     *
     * @throws InvalidInputException
     * @throws ServiceException
     * @throws ORMException
     */
    public function __invoke(UntrackCharacterCommand $command)
    {
        /*
         * Find an active tracker for characterSource
         *  if found
         *      end it
         *  if not found
         *      throw exception, to untrack a character it must first be tracked
         *
         * Verify if any active trackers are left
         *  if no trackers
         *      end character, end claims, end guild
         *  if trackers left
         *      do nothing
         */

        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        // create a shared $fromTime since we will need it often below
        $endTime = new DateTime();

        /** @var CharacterSessionImpl $characterSessionImpl */
        $characterSessionImpl = $command->getCharacterSession();

        $em = $this->doctrine->getManager();

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
                    "The character %s is not being tracked by current characterSource, cannot untrack it",
                    $command->getCharacterId()
                ),
                400
            );
        }

        // close the TrackedBy held by this characterSource
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->update(CharacterOriginEntity\TrackedBy::class, 'trackedBy')
            ->set('trackedBy.endTime', '?1')
            ->where($qb->expr()->eq('trackedBy.character', '?2'))
            ->setParameter(1, $endTime)
            ->setParameter(2, $em->getReference(CharacterEntity\Character::class, $command->getCharacterId()))
            ->getQuery()->execute();

        // search for any other active trackers
        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('trackedBy')
            ->from(CharacterOriginEntity\TrackedBy::class, 'trackedBy')
            ->where('trackedBy.character = ?1')
            ->andWhere('trackedBy.fromTime IS NOT NULL')
            ->andWhere('trackedBy.endTime IS NULL')
            ->setParameter(
                1,
                $em->getReference(CharacterEntity\Character::class, $command->getCharacterId())
            );

        /* @var $query Query */
        $query = $qb->getQuery();

        $trackedBys = $query->getResult();

        if (count($trackedBys) == 0)
        {
            // nobody else is tracking this character, clean up

            // close the character
            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();

            $qb->update(CharacterEntity\Character::class, 'char')
                ->set('char.endTime', '?1')
                ->where('char.id = ?2')
                ->setParameter(1, $endTime)
                ->setParameter(2, $command->getCharacterId())
                ->getQuery()->execute();

            // close all character versions (should only be one)
            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();

            $qb->update(CharacterEntity\CharacterVersion::class, 'charVersion')
                ->set('charVersion.endTime', '?1')
                ->where('charVersion.character = ?2')
                ->setParameter(1, $endTime)
                ->setParameter(2, $em->getReference(CharacterEntity\Character::class, $command->getCharacterId()))
                ->getQuery()->execute();

            // close all claims
            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();

            $qb->update(CharacterEntity\Claim::class, 'claim')
                ->set('claim.endTime', '?1')
                ->where('claim.character = ?2')
                ->setParameter(1, $endTime)
                ->setParameter(2, $em->getReference(CharacterEntity\Character::class, $command->getCharacterId()))
                ->getQuery()->execute();

            // close all PlayRoles associated with above claims

            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();

            /** @var QueryBuilder $innerQb */
            $innerQb = $em->createQueryBuilder();

            $qb->update(CharacterEntity\PlaysRole::class, 'playsRole')
                ->set('playsRole.endTime', '?1')
                ->where(
                    $qb->expr()->in(
                        'playsRole.claim',
                        $innerQb->select('claim.id')
                            ->from(CharacterEntity\Claim::class, 'claim')
                            ->add('where',
                                $innerQb->expr()->eq('claim.character', '?2')
                            )->getDQL()
                    )
                )
                ->setParameter(1, $endTime)
                ->setParameter(2, $em->getReference(CharacterEntity\Character::class, $command->getCharacterId()))
                ->getQuery()->execute();

            // close InGuild it if exists
            /** @var QueryBuilder $qb */
            $qb = $em->createQueryBuilder();

            $qb->update(CharacterEntity\InGuild::class, 'inGuild')
                ->set('inGuild.endTime', '?1')
                ->where('inGuild.character = ?2')
                ->setParameter(1, $endTime)
                ->setParameter(2, $em->getReference(CharacterEntity\Character::class, $command->getCharacterId()))
                ->getQuery()->execute();
        }

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::CHARACTER_UNTRACK,
                $account,
                [
                    'accountId'      => $account !== null ? $account->getId() : null,
                    'characterId'    => $command->getCharacterId()
                ]
            )
        );
    }
}
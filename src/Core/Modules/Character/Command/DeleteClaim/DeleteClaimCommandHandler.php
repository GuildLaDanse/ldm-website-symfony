<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\DeleteClaim;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Repository\Character\ClaimRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;

class DeleteClaimCommandHandler implements MessageHandlerInterface
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
     * @var AuthorizationService
     */
    private AuthorizationService $authzService;

    /**
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $doctrine
     * @param AuthenticationService $authenticationService
     * @param AuthorizationService $authzService
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        AuthenticationService $authenticationService,
        AuthorizationService $authzService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->authenticationService = $authenticationService;
        $this->authzService = $authzService;
    }

    /**
     * @param DeleteClaimCommand $command
     *
     * @return void
     *
     * @throws ServiceException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(DeleteClaimCommand $command): void
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        // create a shared $fromTime since we will need it often below
        $onDateTime = new DateTime();

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /*
         * Search for an active claim for the given character
         * If found
         *      Verify if the current account is authorized to delete it
         *      If yes
         *          set the endTime on the claim, claimVersion and any playsRole
         *      If no
         *          throw UnauthorizedException
         * Not found
         *      throw ServiceException (404)
         */

        /** @var ClaimRepository $claimRepository */
        $claimRepository = $em->getRepository(CharacterEntity\Claim::class);

        $claims = $claimRepository->findActiveClaimsByCharacter($command->getCharacterId());

        if (count($claims) == 0)
        {
            throw new ServiceException(
                sprintf('Could not find an active claim for character %s', $command->getCharacterId()),
                404
            );
        }
        else if (count($claims) > 1)
        {
            throw new ServiceException(
                sprintf('There are too many claims for character %s', $command->getCharacterId()),
                500
            );
        }

        // count($claims) == 1, we can remove the Claim

        /** @var CharacterEntity\Claim $claim */
        $claim = $claims[0];

        /* verify that the user can edit this particular event */
        $this->authzService->allowOrThrow(
            new SubjectReference($account),
            ActivityType::CLAIM_REMOVE,
            new ResourceByValue(CharacterEntity\Claim::class, $claim));

        $claim->setEndTime($onDateTime);

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->update(CharacterEntity\ClaimVersion::class, 'claimVersion')
            ->set('claimVersion.endTime', '?1')
            ->where($qb->expr()->eq('claimVersion.claim', '?2'))
            ->andWhere('claimVersion.endTime IS NULL')
            ->setParameter(1, $onDateTime)
            ->setParameter(2, $claim)
            ->getQuery()->execute();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->update(CharacterEntity\PlaysRole::class, 'playsRole')
            ->set('playsRole.endTime', '?1')
            ->where($qb->expr()->eq('playsRole.claim', '?2'))
            ->andWhere('playsRole.endTime IS NULL')
            ->setParameter(1, $onDateTime)
            ->setParameter(2, $claim)
            ->getQuery()->execute();

        $em->flush();

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::CLAIM_REMOVE,
                $account,
                [
                    'accountId'   => $account !== null ? $account->getId() : null,
                    'characterId' => $command->getCharacterId()
                ]
            )
        );
    }
}
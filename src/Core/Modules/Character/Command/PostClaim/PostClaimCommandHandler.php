<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\PostClaim;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Security\AuthenticationService;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;

class PostClaimCommandHandler
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
     * PostClaimCommandHandler constructor.
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
     * @param PostClaimCommand $command
     * @throws InvalidInputException
     */
    protected function validateInput(PostClaimCommand $command): void
    {
        if ($command->getPatchClaim() === null)
        {
            throw new InvalidInputException("patchClaim can't be null");
        }
    }

    /**
     * @param PostClaimCommand $command
     *
     * @return void
     *
     * @throws ServiceException
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(PostClaimCommand $command): void
    {
        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        // create a shared $fromTime since we will need it often below
        $fromTime = new DateTime();

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /*
         * Verify if there is an active claim for this character
         *  If yes
         *      Throw exception that the character is already claimed
         *  If no
         *      Create claim using the information found in PatchClaim
         *
         */

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('claim', 'character', 'account')
            ->from(CharacterEntity\Claim::class, 'claim')
            ->join('claim.character', 'character')
            ->join('claim.account', 'account')
            ->where('character.id = ?1')
            ->andWhere('claim.fromTime IS NOT NULL')
            ->andWhere('claim.endTime IS NULL')
            ->setParameter(1, $command->getCharacterId());

        /* @var Query $dbQuery */
        $dbQuery = $qb->getQuery();

        $claims = $dbQuery->getResult();

        if (count($claims) == 1)
        {
            throw new ServiceException(
                'There is already a claim for this character',
                400
            );
        }
        else if (count($claims) > 1)
        {
            throw new ServiceException(
                'There are multiple active claims for this character',
                500
            );
        }

        // count($claims) == 0, we can create a new Claim

        $claim = new CharacterEntity\Claim();
        $claim
            ->setAccount($em->getReference(Account::class, $command->getAccountId()))
            ->setCharacter($em->getReference(CharacterEntity\Character::class, $command->getCharacterId()))
            ->setFromTime($fromTime)
            ->setEndTime(null);

        $this->persistPlaysRoles($em, $fromTime, $command, $claim);

        $claimVersion = new CharacterEntity\ClaimVersion();
        $claimVersion
            ->setClaim($claim)
            ->setFromTime($fromTime)
            ->setEndTime(null)
            ->setRaider($command->getPatchClaim()->isRaider())
            ->setComment($command->getPatchClaim()->getComment());

        $em->persist($claim);
        $em->persist($claimVersion);
        $em->flush();

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::CLAIM_CREATE,
                $account,
                [
                    'accountId'   => $account !== null ? $account->getId() : null,
                    'characterId' => $command->getCharacterId(),
                    'patchClaim'  => ActivityEvent::annotatedToSimpleObject($command->getPatchClaim())
                ]
            )
        );
    }

    private function createPlaysRole($onDateTime, $claim, $role)
    {
        $playsRole = new CharacterEntity\PlaysRole();
        $playsRole
            ->setRole($role)
            ->setClaim($claim)
            ->setFromTime($onDateTime);

        return $playsRole;
    }

    /**
     * @param ObjectManager $em
     * @param DateTime $fromTime
     * @param PostClaimCommand $command
     * @param CharacterEntity\Claim $claim
     *
     * @throws ServiceException
     */
    protected function persistPlaysRoles(
        ObjectManager $em,
        DateTime $fromTime,
        PostClaimCommand $command,
        CharacterEntity\Claim $claim)
    {
        $isDps = false;
        $isHealer = false;
        $isTank = false;

        foreach ($command->getPatchClaim()->getRoles() as $strRole) {
            $checkedRole = null;

            if ($strRole == CharacterEntity\Role::DPS) {
                if ($isDps)
                    throw new ServiceException(
                        sprintf("role %s can only be claimed once", $strRole),
                        400
                    );

                $checkedRole = CharacterEntity\Role::DPS;

                $isDps = true;
            } else if ($strRole == CharacterEntity\Role::TANK) {
                if ($isTank)
                    throw new ServiceException(
                        sprintf("role %s can only be claimed once", $strRole),
                        400
                    );

                $checkedRole = CharacterEntity\Role::TANK;

                $isTank = true;
            } else if ($strRole == CharacterEntity\Role::HEALER) {
                if ($isHealer)
                    throw new ServiceException(
                        sprintf("role %s can only be claimed once", $strRole),
                        400
                    );

                $checkedRole = CharacterEntity\Role::HEALER;

                $isHealer = true;
            } else {
                throw new ServiceException(
                    sprintf("%s is not a recognized role", $strRole),
                    400
                );
            }

            $em->persist($this->createPlaysRole($fromTime, $claim, $checkedRole));
        }
    }
}
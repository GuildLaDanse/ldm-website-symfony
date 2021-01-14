<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Command\PutClaim;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Authorization\CannotEvaluateException;
use App\Infrastructure\Authorization\ResourceByValue;
use App\Infrastructure\Authorization\SubjectReference;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Domain\Entity\Character as CharacterEntity;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PutClaimCommandHandler implements MessageHandlerInterface
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
     * PostClaimCommandHandler constructor.
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
     * @param PutClaimCommand $command
     *
     * @throws InvalidInputException
     */
    private function validateInput(PutClaimCommand $command): void
    {
        if ($command->getPatchClaim() === null)
        {
            throw new InvalidInputException("patchClaim can't be null");
        }
    }

    /**
     * @param PutClaimCommand $command
     *
     * @throws InvalidInputException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ServiceException
     * @throws CannotEvaluateException
     */
    public function __invoke(PutClaimCommand $command)
    {
        $this->validateInput($command);

        /** @var Account $account */
        $account = $this->authenticationService->getCurrentContext()->getAccount();

        // create a shared $onDateTime since we will need it often below
        $onDateTime = new DateTime();

        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /*
         * Search for an active claim for the given character
         * If found
         *      Verify if the current account is authorized to change it
         *      If yes
         *          Update it
         *      If no
         *          throw UnauthorizedException
         * Not found
         *      throw ServiceException (404)
         */

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('claimVersion', 'claim', 'character', 'account')
            ->from(CharacterEntity\ClaimVersion::class, 'claimVersion')
            ->join('claimVersion.claim', 'claim')
            ->join('claim.character', 'character')
            ->join('claim.account', 'account')
            ->where('character.id = ?1')
            ->andWhere('claimVersion.fromTime IS NOT NULL')
            ->andWhere('claimVersion.endTime IS NULL')
            ->setParameter(1, $command->getCharacterId());

        /* @var Query $query */
        $query = $qb->getQuery();

        $claimVersions = $query->getResult();

        if (count($claimVersions) == 0)
        {
            throw new ServiceException(
                sprintf('Could not find an active claim for character %s', $command->getCharacterId()),
                404
            );
        }
        else if (count($claimVersions) > 1)
        {
            throw new ServiceException(
                sprintf('There are too many claims for character %s', $command->getCharacterId()),
                500
            );
        }

        /** @var CharacterEntity\ClaimVersion $claimVersion */
        $claimVersion = $claimVersions[0];

        /* verify that the user can edit this particular event */
        if (!$this->authzService->evaluate(
            new SubjectReference($account),
            ActivityType::CLAIM_EDIT,
            new ResourceByValue(CharacterEntity\Claim::class, $claimVersion->getClaim())))
        {
            $this->logger->warning(__CLASS__ . ' the user is not authorized to edit event in indexAction');

            throw new ServiceException(
                "You are not allowed to update this claim",
                403
            );
        }

        $claimVersion->setEndTime($onDateTime);

        $newClaimVersion = new CharacterEntity\ClaimVersion();
        $newClaimVersion
            ->setClaim($claimVersion->getClaim())
            ->setFromTime($onDateTime)
            ->setEndTime(null)
            ->setRaider($command->getPatchClaim()->isRaider())
            ->setComment($command->getPatchClaim()->getComment());

        $em->persist($newClaimVersion);

        $this->updatePlaysRoles($em, $command, $claimVersion->getClaim(), $onDateTime);

        $em->flush();

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::CLAIM_EDIT,
                $account,
                [
                    'accountId'   => $account !== null ? $account->getId() : null,
                    'characterId' => $command->getCharacterId(),
                    'patchClaim'  => ActivityEvent::annotatedToSimpleObject($command->getPatchClaim())
                ]
            )
        );
    }

    /**
     * @param EntityManager $em
     * @param PutClaimCommand $command
     * @param CharacterEntity\Claim $claim
     * @param DateTime $onDateTime
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ServiceException
     */
    private function updatePlaysRoles(EntityManager $em, PutClaimCommand $command, CharacterEntity\Claim $claim, DateTime $onDateTime)
    {
        $isDps = false;
        $isHealer = false;
        $isTank = false;

        foreach ($command->getPatchClaim()->getRoles() as $strRole)
        {
            $checkedRole = null;

            if ($strRole == CharacterEntity\Role::DPS)
            {
                $isDps = true;
            }
            else if ($strRole == CharacterEntity\Role::TANK)
            {
                $isTank = true;
            }
            else if ($strRole == CharacterEntity\Role::HEALER)
            {
                $isHealer = true;
            }
            else
            {
                throw new ServiceException(
                    sprintf("%s is not a recognized role", $strRole),
                    400
                );
            }
        }

        $this->checkAndUpdateRole($em, $claim, CharacterEntity\Role::DPS, $isDps, $onDateTime);
        $this->checkAndUpdateRole($em, $claim, CharacterEntity\Role::TANK, $isTank, $onDateTime);
        $this->checkAndUpdateRole($em, $claim, CharacterEntity\Role::HEALER, $isHealer, $onDateTime);
    }


    /**
     * @param EntityManager $em
     * @param CharacterEntity\Claim $claim
     * @param $roleName
     * @param $willPlayRole
     * @param $onDateTime
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function checkAndUpdateRole(EntityManager $em, CharacterEntity\Claim $claim, $roleName, $willPlayRole, $onDateTime)
    {
        $alreadyPlaysRole = false;

        /* @var $playsRole CharacterEntity\\PlaysRole */
        foreach($claim->getRoles() as $playsRole)
        {
            // remember if the role is currently active (present and endTime not set)
            if ($playsRole->isRole($roleName) && ($playsRole->getEndTime() === null))
            {
                $alreadyPlaysRole = true;
            }
        }

        if (!$alreadyPlaysRole && $willPlayRole)
        {
            $em->persist($this->createPlaysRole($onDateTime, $claim, $roleName));

            $this->logger->info(__CLASS__ . ' added ' . $roleName . ' role to claim ' . $claim->getId());
        }

        /* @var $playsRole CharacterEntity\PlaysRole */
        foreach($claim->getRoles() as $playsRole)
        {
            // if the role is currently active (present and endTime not set)
            // and the player will not play it anymore, set endTime
            if ($playsRole->isRole($roleName) && ($playsRole->getEndTime() === null) && !$willPlayRole)
            {
                $playsRole->setEndTime($onDateTime);

                $this->logger->info(__CLASS__ . ' removed ' . $roleName . ' role from claim ' . $claim->getId());
            }
        }

        $em->flush();
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
}
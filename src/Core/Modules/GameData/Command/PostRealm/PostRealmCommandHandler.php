<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\Command\PostRealm;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Domain\Entity\GameData as GameDataEntity;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\GameData\DTO as GameDataDTO;
use App\Core\Modules\GameData\DTO\RealmMapper;
use App\Core\Modules\GameData\RealmAlreadyExistsException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostRealmCommandHandler implements MessageHandlerInterface
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
     * PostGuildCommandHandler constructor.
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
     * @param PostRealmCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PostRealmCommand $command)
    {
        if ($command->getPatchRealm() === null || $command->getPatchRealm()->getName() === null)
        {
            throw new InvalidInputException("Given PatchRealm was null or name of realm was null");
        }
    }

    /**
     * @param PostRealmCommand $command
     *
     * @return GameDataDTO\Realm
     *
     * @throws InvalidInputException
     * @throws RealmAlreadyExistsException
     */
    public function __invoke(PostRealmCommand $command): GameDataDTO\Realm
    {
        $this->validateInput($command);

        /** @var Account|null $account */
        $account = $this->authenticationService->getCurrentContext()->isAuthenticated()
            ? $this->authenticationService->getCurrentContext()->getAccount() : null;

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('r')
            ->from('LaDanse\DomainBundle\Entity\GameData\Realm', 'r')
            ->where('r.name = ?1')
            ->setParameter(1, $command->getPatchRealm()->getName());

        $this->logger->debug(
            __CLASS__ . " created DQL for retrieving Realm by name ",
            [
                "query" => $qb->getDQL()
            ]
        );

        /* @var $query Query */
        $query = $qb->getQuery();

        $realms = $query->getResult();

        if (count($realms) !== null)
        {
            throw new RealmAlreadyExistsException(
                "Realm with name '" . $command->getPatchRealm()->getName() . "' already exists", 400
            );
        }

        /* verify that the user is allowed to create a realm */
        /*
         * Disable until we have proper support for Commands.
         *
        if (!$this->authzService->evaluate(
            new SubjectReference($this->getAccount()),
            ActivityType::REALM_CREATE,
            new ResourceByValue(DTO\GameData\PatchRealm::class, null, $this->getPatchRealm())))
        {
            $this->logger->warning(__CLASS__ . ' the user is not authorized to create a realm',
                [
                    "account" => $this->getAccount()->getId(),
                    "realm" => $this->getPatchRealm()->getName()
                ]
            );

            throw new NotAuthorizedException("Current user is not allowed to create a new realm", 401);
        }
         */

        $newRealm = new GameDataEntity\Realm();

        $newRealm->setName($command->getPatchRealm()->getName());

        $em->persist($newRealm);
        $em->flush();

        $dtoRealm = RealmMapper::mapSingle($newRealm);

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::REALM_CREATE,
                $account,
                [
                    'accountId'  => $account,
                    'patchRealm' => ActivityEvent::annotatedToSimpleObject($command->getPatchRealm())
                ]
            )
        );

        return $dtoRealm;
    }
}
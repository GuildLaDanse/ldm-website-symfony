<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\Command\PostGuild;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Authorization\AuthorizationService;
use App\Infrastructure\Modules\InvalidInputException;
use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Domain\Entity\GameData as GameDataEntity;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use App\Core\Modules\GameData\DTO as GameDataDTO;
use App\Core\Modules\GameData\DTO\GuildMapper;
use App\Core\Modules\GameData\GuildAlreadyExistsException;
use App\Core\Modules\GameData\RealmDoesNotExistException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PostGuildCommandHandler implements MessageHandlerInterface
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
     * @param PostGuildCommand $command
     *
     * @throws InvalidInputException
     */
    protected function validateInput(PostGuildCommand $command)
    {
        if ($command->getPatchGuild() === null
            || $command->getPatchGuild()->getName() === null
            || $command->getPatchGuild()->getRealmId() === null
        )
        {
            throw new InvalidInputException("Given GuildRealm was null or properties were null");
        }
    }

    /**
     * @param PostGuildCommand $command
     *
     * @return GameDataDTO\Guild
     *
     * @throws GuildAlreadyExistsException
     * @throws RealmDoesNotExistException
     * @throws InvalidInputException
     */
    public function __invoke(PostGuildCommand $command): GameDataDTO\Guild
    {
        $this->validateInput($command);

        /** @var Account|null $account */
        $account = $this->authenticationService->getCurrentContext()->isAuthenticated()
            ? $this->authenticationService->getCurrentContext()->getAccount() : null;

        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('g', 'r')
            ->from(GameDataEntity\Guild::class, 'g')
            ->leftJoin('g.realm', 'r')
            ->where('g.name = collate(?1, utf8_bin)')
            ->andWhere('r.id = ?2')
            ->setParameter(1, $command->getPatchGuild()->getName())
            ->setParameter(2, $command->getPatchGuild()->getRealmId());

        $this->logger->debug(
            __CLASS__ . " created DQL for retrieving Guild by name and realm",
            [
                "query" => $qb->getDQL()
            ]
        );

        /* @var $query Query */
        $query = $qb->getQuery();

        $guilds = $query->getResult();

        if (count($guilds) !== null)
        {
            throw new GuildAlreadyExistsException(
                "Guild with name '"
                . $command->getPatchGuild()->getName()
                . "' on realm '"
                . $command->getPatchGuild()->getRealmId()
                . "' already exists", 400
            );
        }

        /* verify that the user is allowed to create a guild */
        /*
         * Disable until we have proper support for Commands.
         *
        if (!$this->authzService->evaluate(
            new SubjectReference($this->getAccount()),
            ActivityType::REALM_CREATE,
            new ResourceByValue(DTO\GameData\PatchGuild::class, null, $this->getPatchGuild())))
        {
            $this->logger->warning(__CLASS__ . ' the user is not authorized to create a guild',
                [
                    "account" => $this->getAccount()->getId()
                ]
            );

            throw new NotAuthorizedException("Current user is not allowed to create a new realm", 401);
        }
        */

        /** @var EntityRepository $realmRepo */
        $realmRepo = $em->getRepository(GameDataEntity\Realm::class);

        /** @var GameDataEntity\Realm $realm */
        $realm = $realmRepo->find($command->getPatchGuild()->getRealmId());

        if ($realm === null)
        {
            throw new RealmDoesNotExistException(
                "Realm with id '" . $command->getPatchGuild()->getRealmId() . "' does not exist",
                400
            );
        }

        $newGuild = new GameDataEntity\Guild();

        $newGuild->setName($command->getPatchGuild()->getName());
        $newGuild->setRealm($realm);

        $em->persist($newGuild);
        $em->flush();

        $dtoGuild = GuildMapper::mapSingle($newGuild);

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::GUILD_CREATE,
                $account,
                [
                    'accountId'  => $account !== null ? $account->getId() : null,
                    'patchGuild' => ActivityEvent::annotatedToSimpleObject($command->getPatchGuild())
                ]
            )
        );

        return $dtoGuild;
    }
}
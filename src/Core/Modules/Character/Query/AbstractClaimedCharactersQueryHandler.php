<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Character\Query;

use App\Infrastructure\Security\AuthenticationService;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Common\MapperException;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\Entity\Character as CharacterEntity;
use App\Core\Modules\Character\DTO as CharacterDTO;

abstract class AbstractClaimedCharactersQueryHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var ManagerRegistry
     */
    protected ManagerRegistry $doctrine;

    /**
     * @var CharacterHydrator
     */
    protected CharacterHydrator $characterHydrator;

    /**
     * @var AuthenticationService
     */
    protected AuthenticationService $authenticationService;

    /**
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry $doctrine
     * @param CharacterHydrator $characterHydrator
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        CharacterHydrator $characterHydrator,
        AuthenticationService $authenticationService)
    {
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->doctrine = $doctrine;
        $this->characterHydrator = $characterHydrator;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @param DateTime $onDateTime
     * @param array $characterVersions
     *
     * @return array
     *
     * @throws MapperException
     */
    protected function prepareCharacterResult(DateTime $onDateTime, array $characterVersions): array
    {
        $characterIds = [];

        foreach ($characterVersions as $characterVersion) {
            /** @var CharacterEntity\CharacterVersion $characterVersion */

            $characterIds[] = $characterVersion->getCharacter()->getId();
        }

        $this->characterHydrator->setCharacterIds($characterIds);
        $this->characterHydrator->setOnDateTime($onDateTime);

        return CharacterDTO\CharacterMapper::mapArray($characterVersions, $this->characterHydrator);
    }
}
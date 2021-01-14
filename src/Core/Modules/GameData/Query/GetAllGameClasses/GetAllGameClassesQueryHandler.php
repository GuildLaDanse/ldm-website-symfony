<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\Query\GetAllGameClasses;

use App\Domain\Entity\GameData as GameDataEntity;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\GameData\DTO\GameClassMapper;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class GetAllGameClassesQueryHandler implements MessageHandlerInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * PostGuildCommandHandler constructor.
     * @param LoggerInterface $logger
     * @param ManagerRegistry $doctrine
     */
    public function __construct(
        LoggerInterface $logger,
        ManagerRegistry $doctrine)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }

    /**
     * @param GetAllGameClassesQuery $query
     *
     * @return array
     *
     * @throws MapperException
     */
    public function __invoke(GetAllGameClassesQuery $query): array
    {
        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('g')
            ->from(GameDataEntity\GameClass::class, 'g')
            ->orderBy('g.name', 'ASC');

        $this->logger->debug(
            __CLASS__ . ' created DQL for retrieving GameClasses ',
            [
                'query' => $qb->getDQL()
            ]
        );

        /** @var Query $dbQuery */
        $dbQuery = $qb->getQuery();

        $gameClasses = $dbQuery->getResult();

        return GameClassMapper::mapArray($gameClasses);
    }
}
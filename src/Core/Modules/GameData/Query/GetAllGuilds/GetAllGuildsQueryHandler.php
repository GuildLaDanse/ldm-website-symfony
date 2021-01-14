<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\GameData\Query\GetAllGuilds;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Core\Modules\Common\MapperException;
use App\Core\Modules\GameData\DTO\GuildMapper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class GetAllGuildsQueryHandler implements MessageHandlerInterface
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
     * @param GetAllGuildsQuery $query
     *
     * @return array
     *
     * @throws MapperException
     */
    public function __invoke(GetAllGuildsQuery $query)
    {
        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('g')
            ->from('LaDanse\DomainBundle\Entity\GameData\Guild', 'g')
            ->orderBy('g.name', 'ASC');

        $this->logger->debug(
            __CLASS__ . " created DQL for retrieving Guilds ",
            [
                "query" => $qb->getDQL()
            ]
        );

        /** @var Query $dbQuery */
        $dbQuery = $qb->getQuery();

        $dbQuery->setFetchMode('LaDanse\DomainBundle\Entity\GameData\Realm', 'realm', ClassMetadata::FETCH_EAGER);

        $guilds = $dbQuery->getResult();

        return GuildMapper::mapArray($guilds);
    }
}
<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum;

use App\Domain\Entity\Account\Account;
use App\Infrastructure\Modules\UUIDUtils;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;
use App\Domain\Entity\Forum as ForumEntity;

class ForumStatsService
{
    /**
     * @var Registry
     */
    private Registry $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param DateTime $sinceDateTime
     *
     * @return array
     */
    public function getNewPostsSince($sinceDateTime)
    {
        $em = $this->doctrine->getManager();

        /* @var $query Query */
        $query = $em->createQuery(
            <<<'EOD'
                SELECT p, t, f
                FROM App\Entity\Forum\Post p
                    LEFT JOIN p.topic t
                    LEFT JOIN t.forum f
                WHERE
                    (p.postDate > :sinceDateTime)
            EOD
        );
        $query->setParameter('sinceDateTime', $sinceDateTime);

        return $query->getResult();
    }

    /**
     * @param Account $account
     *
     * @return array

     * @throws Exception
     */
    public function getUnreadPostsForAccount(Account $account)
    {
        $em = $this->doctrine->getManager();

        $lastVisit = $this->getLastVisitForAccount($account, new DateTime());

        $newPosts = $this->getNewPostsSince($lastVisit);

        /** @var ForumEntity\Post $newPost */
        foreach($newPosts as $newPost)
        {
            if ($newPost->getPoster()->getId() == $account->getId())
            {
                continue;
            }

            $unreadPost = new ForumEntity\UnreadPost();
            $unreadPost->setId(UUIDUtils::createUUID());
            $unreadPost->setAccount($account);
            $unreadPost->setPost($newPost);

            $em->persist($unreadPost);
        }

        $em->flush();

        $this->resetLastVisitForAccount($account);

        /* @var $query Query */
        $query = $em->createQuery(
            <<<'EOD'
                SELECT u, p, t, f
                FROM App\Entity\Forum\UnreadPost u
                    LEFT JOIN u.post p
                    LEFT JOIN p.topic t
                    LEFT JOIN t.forum f
                WHERE
                    (u.account = :forAccount)
                    AND
                    (p.poster != :forAccount)
            EOD
        );
        $query->setParameter('forAccount', $account);

        $queryResult = $query->getResult();

        $unreadPosts = [];

        /** @var ForumEntity\UnreadPost $unreadPost */
        foreach($queryResult as $unreadPost)
        {
            $unreadPosts[] = $unreadPost->getPost();
        }

        return $unreadPosts;
    }

    /**
     * @param DateTime $sinceDateTime
     *
     * @return array
     */
    public function getNewTopicsSince($sinceDateTime)
    {
        $em = $this->doctrine->getManager();

        /* @var $query Query */
        $query = $em->createQuery(
            <<<'EOD'
                SELECT t, f
                FROM App\Entity\Forum\Topic t
                    LEFT JOIN t.forum f
                WHERE
                    (t.createDate > :sinceDateTime)
            EOD
        );
        $query->setParameter('sinceDateTime', $sinceDateTime);

        return $query->getResult();
    }

    /**
     * @param Account $account
     * @param string $postId
     */
    public function markPostAsRead(Account $account, $postId)
    {
        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->delete('LaDanse\DomainBundle\Entity\Forum\UnreadPost', 'u')
           ->where('u.post = :readPost')
           ->andWhere('u.account = :forAccount')
           ->setParameter('readPost', $postId)
           ->setParameter('forAccount', $account);

        $query = $qb->getQuery();

        $query->getResult();
    }

    /**
     * @param Account $account
     * @param DateTime $default
     *
     * @return DateTime
     */
    private function getLastVisitForAccount($account, DateTime $default = null)
    {
        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('v')
            ->from('LaDanse\DomainBundle\Entity\Forum\ForumLastVisit', 'v')
            ->where(
                $qb->expr()->eq('v.account', '?1')
            )
            ->setParameter(1, $account);

        $query = $qb->getQuery();
        $result = $query->getResult();

        if (count($result) == 0)
        {
            return $default;
        }
        else
        {
            /** @var ForumEntity\ForumLastVisit $forumLastVisit */
            $forumLastVisit = $result[0];

            return $forumLastVisit->getLastVisitDate();
        }
    }

    /**
     * @param Account $account
     *
     * @throws Exception
     */
    private function resetLastVisitForAccount($account)
    {
        $em = $this->doctrine->getManager();

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();

        $qb->select('v')
            ->from('LaDanse\DomainBundle\Entity\Forum\ForumLastVisit', 'v')
            ->where(
                $qb->expr()->eq('v.account', '?1')
            )
            ->setParameter(1, $account);

        $query = $qb->getQuery();
        $result = $query->getResult();

        if (count($result) == 0)
        {
            $forumLastVisit = new ForumEntity\ForumLastVisit();
            $forumLastVisit->setId(UUIDUtils::createUUID());
            $forumLastVisit->setAccount($account);
            $forumLastVisit->setLastVisitDate(new DateTime());

            $em->persist($forumLastVisit);
            $em->flush();
        }
        else
        {
            /** @var ForumEntity\ForumLastVisit $forumLastVisit */
            $forumLastVisit = $result[0];

            $forumLastVisit->setLastVisitDate(new DateTime());

            $em->persist($forumLastVisit);
            $em->flush();
        }
    }
}

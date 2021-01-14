<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum;

use App\Domain\Entity\Account\Account;
use App\Domain\Entity\Forum as ForumEntity;
use App\Infrastructure\Modules\UUIDUtils;
use App\Infrastructure\Security\AuthenticationService;
use App\Core\Modules\Activity\ActivityEvent;
use App\Core\Modules\Activity\ActivityType;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Query;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ForumService
{
    /**
     * @var Registry
     */
    private Registry $doctrine;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var AuthenticationService
     */
    private AuthenticationService $authenticationService;

    /**
     * @param Registry $doctrine
     * @param EventDispatcherInterface $eventDispatcher
     * @param AuthenticationService $authenticationService
     */
    public function __construct(
        Registry $doctrine,
        EventDispatcherInterface $eventDispatcher,
        AuthenticationService $authenticationService)
    {
        $this->doctrine = $doctrine;
        $this->eventDispatcher = $eventDispatcher;
        $this->authenticationService = $authenticationService;
    }

    /**
     * @return array
     */
    public function getAllForums()
    {
        $forumRepo = $this->doctrine->getRepository(ForumEntity\Forum::class);

        return $forumRepo->findAll();
    }

    /**
     * @param $forumId
     *
     * @return ForumEntity\Forum
     *
     * @throws ForumDoesNotExistException
     */
    public function getForum($forumId)
    {
        $forumRepo = $this->doctrine->getRepository(ForumEntity\Forum::class);

        /** @var ForumEntity\Forum $forum */
        $forum = $forumRepo->find($forumId);

        if (null === $forum)
        {
            throw new ForumDoesNotExistException("Forum does not exist: " . $forumId);
        }
        else
        {
            return $forum;
        }
    }

    /**
     * @return array
     */
    public function getActivityForForums()
    {
        $em = $this->doctrine->getManager();

        /* @var Query $query */
        $query = $em->createQuery(
            <<<'EOD'
                SELECT p, t, f
                FROM App\Entity\Forum\Post p
                    LEFT JOIN p.topic t
                    LEFT JOIN t.forum f
                ORDER BY p.postDate DESC
            EOD
        );
        $query->setMaxResults(10);

        return $query->getResult();
    }

    /**
     * @param $forumId
     *
     * @return array
     */
    public function getActivityForForum($forumId)
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
                    (f.id = :forumId)
                ORDER BY p.postDate DESC
            EOD
        );
        $query->setParameter('forumId', $forumId);
        $query->setMaxResults(10);

        return $query->getResult();
    }

    /**
     * @param $forumId
     *
     * @return array
     *
     * @throws ForumDoesNotExistException
     */
    public function getAllTopicsInForum($forumId)
    {
        $topicRepo = $this->doctrine->getRepository(ForumEntity\Forum::class);

        /** @var ForumEntity\Forum $forum */
        $forum = $topicRepo->find($forumId);

        if (null === $forum)
        {
            throw new ForumDoesNotExistException("Forum does not exist: " . $forumId);
        }
        else
        {
            $result = [];

            $topics = $forum->getTopics();

            foreach($topics as $topic)
            {
                $result[] = $topic;
            }

            return $result;
        }
    }

    /**
     * @param $topicId
     *
     * @return array
     *
     * @throws TopicDoesNotExistException
     */
    public function getAllPosts($topicId)
    {
        $topicRepo = $this->doctrine->getRepository(ForumEntity\Topic::class);

        $topic = $topicRepo->find($topicId);

        if (null === $topic)
        {
            throw new TopicDoesNotExistException("Topic does not exist: " . $topicId);
        }
        else
        {
            $result = [];

            $posts = $topic->getPosts();

            foreach($posts as $post)
            {
                $result[] = $post;
            }

            return $result;
        }
    }

    /**
     * @param $postId
     *
     * @return ForumEntity\Post
     *
     * @throws PostDoesNotExistException
     */
    public function getPost($postId)
    {
        $postRepo = $this->doctrine->getRepository(ForumEntity\Post::class);

        /** @var ForumEntity\Post $post */
        $post = $postRepo->find($postId);

        if (null === $post)
        {
            throw new PostDoesNotExistException("Post does not exist: " . $postId);
        }
        else
        {
            return $post;
        }
    }

    /**
     * @param $topicId
     *
     * @return ForumEntity\Topic
     *
     * @throws TopicDoesNotExistException
     */
    public function getTopic($topicId)
    {
        $topicRepo = $this->doctrine->getRepository(ForumEntity\Topic::class);

        /** @var ForumEntity\Topic $topic */
        $topic = $topicRepo->find($topicId);

        if (null === $topic)
        {
            throw new TopicDoesNotExistException("Topic does not exist: " . $topicId);
        }
        else
        {
            return $topic;
        }
    }

    /**
     * @param Account $account
     * @param $forumId
     * @param $subject
     * @param $text
     *
     * @return string
     *
     * @throws ForumDoesNotExistException
     */
    public function createTopicInForum(Account $account, $forumId, $subject, $text)
    {
        $em = $this->doctrine->getManager();

        $forum = $this->getForum($forumId);

        $topicId = UUIDUtils::createUUID();

        $topic = new ForumEntity\Topic();

        $topic->setId($topicId);
        $topic->setCreateDate(new DateTime());
        $topic->setCreator($account);
        $topic->setSubject($subject);
        $topic->setForum($forum);

        $post = new ForumEntity\Post();

        $post->setId(UUIDUtils::createUUID());
        $post->setPostDate(new DateTime());
        $post->setPoster($account);
        $post->setMessage($text);
        $post->setTopic($topic);

        $topic->addPost($post);

        // update last post on Forum
        $forum->setLastPostDate($post->getPostDate());
        $forum->setLastPostPoster($account);
        $forum->setLastPostTopic($topic);

        // update last post on Topic
        $topic->setLastPostDate($post->getPostDate());
        $topic->setLastPostPoster($account);

        $em->persist($post);
        $em->persist($topic);
        $em->flush();

        $this->eventDispatcher->dispatch(
            new ActivityEvent(
                ActivityType::FORUM_TOPIC_CREATE,
                $this->authenticationService->getCurrentContext()->getAccount(),
                [
                    'postedBy' =>
                    [
                        'id'   => $account->getId(),
                        'name' => $account->getDisplayName()
                    ],
                    'topicId'      => $topicId,
                    'topicSubject' => $subject,
                    'message'      => $text,
                    'forumId'      => $forum->getId(),
                    'forumName'    => $forum->getName()
                ]
            ),
            ActivityEvent::EVENT_NAME
        );

        return $topicId;
    }

    /**
     * @param Account $account
     * @param string $topicId
     *
     * @throws TopicDoesNotExistException
     */
    public function removeTopic(Account $account, $topicId)
    {
        $em = $this->doctrine->getManager();

        $topicRepo = $this->doctrine->getRepository(ForumEntity\Topic::class);

        /** @var ForumEntity\Topic $topic */
        $topic = $topicRepo->find($topicId);

        if (null === $topic)
        {
            throw new TopicDoesNotExistException("Topic does not exist: " . $topicId);
        }
        else
        {
            $em->remove($topic);
            $em->flush();

            $this->eventDispatcher->dispatch(
                new ActivityEvent(
                    ActivityType::FORUM_TOPIC_REMOVE,
                    $this->authenticationService->getCurrentContext()->getAccount(),
                    [
                        'removedBy' =>
                        [
                            'id'   => $account->getId(),
                            'name' => $account->getDisplayName()
                        ],
                        'topicId'      => $topicId,
                        'topicSubject' => $topic->getSubject(),
                        'forumId'      => $topic->getForum()->getId(),
                        'forumName'    => $topic->getForum()->getName()
                    ]
                ),
                ActivityEvent::EVENT_NAME
            );
        }
    }

    /**
     * @param Account $account
     * @param $topicId
     * @param $message
     *
     * @throws TopicDoesNotExistException
     */
    public function createPost(Account $account, $topicId, $message)
    {
        $em = $this->doctrine->getManager();
        $topicRepo = $this->doctrine->getRepository(ForumEntity\Topic::class);

        /* @var $topic ForumEntity\Topic */
        $topic = $topicRepo->find($topicId);

        if (null === $topic)
        {
            throw new TopicDoesNotExistException("Topic does not exist: " . $topicId);
        }
        else
        {
            $post = new ForumEntity\Post();

            $post->setId(UUIDUtils::createUUID());
            $post->setPostDate(new DateTime());
            $post->setPoster($account);
            $post->setMessage($message);
            $post->setTopic($topic);

            $topic->addPost($post);

            // update last post on Forum
            $forum = $topic->getForum();
            $forum->setLastPostDate($post->getPostDate());
            $forum->setLastPostPoster($account);
            $forum->setLastPostTopic($topic);

            // update last post on Topic
            $topic->setLastPostDate($post->getPostDate());
            $topic->setLastPostPoster($account);

            $em->persist($post);
            $em->flush();

            $this->eventDispatcher->dispatch(
                new ActivityEvent(
                    ActivityType::FORUM_POST_CREATE,
                    $this->authenticationService->getCurrentContext()->getAccount(),
                    [
                        'postedBy' =>
                        [
                            'id'   => $account->getId(),
                            'name' => $account->getDisplayName()
                        ],
                        'topicId'      => $topicId,
                        'topicSubject' => $topic->getSubject(),
                        'message'      => $message,
                        'forumId'      => $topic->getForum()->getId(),
                        'forumName'    => $topic->getForum()->getName()
                    ]
                ),
                ActivityEvent::EVENT_NAME
            );
        }
    }

    /**
     * @param Account $account
     * @param $postId
     * @param $message
     *
     * @throws PostDoesNotExistException
     */
    public function updatePost(Account $account, $postId, $message)
    {
        $em = $this->doctrine->getManager();
        $postRepo = $this->doctrine->getRepository(ForumEntity\Post::class);

        $post = $postRepo->find($postId);

        $oldMessage = $post->getMessage();

        if (null === $post)
        {
            throw new PostDoesNotExistException("Post does not exist: " . $postId);
        }
        else
        {
            $post->setMessage($message);
            
            $em->persist($post);
            $em->flush();

            $this->eventDispatcher->dispatch(
                new ActivityEvent(
                    ActivityType::FORUM_POST_UPDATE,
                    $this->authenticationService->getCurrentContext()->getAccount(),
                    [
                        'updatedBy' =>
                        [
                            'id'   => $account->getId(),
                            'name' => $account->getDisplayName()
                        ],
                        'postId'       => $postId,
                        'topicId'      => $post->getTopic()->getId(),
                        'topicSubject' => $post->getTopic()->getSubject(),
                        'forumId'      => $post->getTopic()->getForum()->getId(),
                        'forumName'    => $post->getTopic()->getForum()->getName(),
                        'oldMessage'   => $oldMessage,
                        'newMessage'   => $message
                    ]
                ),
                ActivityEvent::EVENT_NAME
            );
        }
    }

    /**
     * @param Account $account
     * @param $topicId
     * @param $subject
     *
     * @throws TopicDoesNotExistException
     */
    public function updateTopic(Account $account, $topicId, $subject)
    {
        $em = $this->doctrine->getManager();
        $topicRepo = $this->doctrine->getRepository(ForumEntity\Topic::class);

        $topic = $topicRepo->find($topicId);

        $oldSubject = $topic->getSubject();

        if (null === $topic)
        {
            throw new TopicDoesNotExistException("Topic does not exist: " . $topicId);
        }
        else
        {
            $topic->setSubject($subject);

            $em->persist($topic);
            $em->flush();

            $this->eventDispatcher->dispatch(
                new ActivityEvent(
                    ActivityType::FORUM_TOPIC_UPDATE,
                    $this->authenticationService->getCurrentContext()->getAccount(),
                    [
                        'updatedBy' =>
                        [
                            'id'   => $account->getId(),
                            'name' => $account->getDisplayName()
                        ],
                        'topicId'      => $topicId,
                        'topicSubject' => $topic->getSubject(),
                        'forumId'      => $topic->getForum()->getId(),
                        'forumName'    => $topic->getForum()->getName(),
                        'oldMessage'   => $oldSubject,
                        'newMessage'   => $subject
                    ]
                ),
                ActivityEvent::EVENT_NAME
            );
        }
    }

    /**
     * Update all last posts
     */
    public function updateLastPosts()
    {
        $em = $this->doctrine->getManager();

        $forums = $this->getAllForums();

        /** @var ForumEntity\Forum $forum */
        foreach($forums as $forum)
        {
            $topics = $forum->getTopics();

            /** @var ForumEntity\Post $lastPostInForum */
            $lastPostInForum = null;

            /** @var ForumEntity\Topic $topic */
            foreach($topics as $topic)
            {
                /** @var ForumEntity\Post $lastPostInTopic */
                $lastPostInTopic = null;

                $posts = $topic->getPosts();

                /** @var ForumEntity\Post $post */
                foreach($posts as $post)
                {
                    // Update $lastPostInTopic
                    if ($lastPostInTopic === null)
                    {
                        $lastPostInTopic = $post;
                    }
                    else if ($post->getPostDate() > $lastPostInTopic->getPostDate())
                    {
                        $lastPostInTopic = $post;
                    }

                    // Update $lastPostInForum
                    if ($lastPostInForum === null)
                    {
                        $lastPostInForum = $post;
                    }
                    else if ($post->getPostDate() > $lastPostInForum->getPostDate())
                    {
                        $lastPostInForum = $post;
                    }
                }

                // Update $lastPostInTopic
                if ($lastPostInTopic !== null)
                {
                    $topic->setLastPostDate($lastPostInTopic->getPostDate());
                    $topic->setLastPostPoster($lastPostInTopic->getPoster());
                }
            }

            // Update $lastPostInTopic
            if ($lastPostInForum !== null)
            {
                $forum->setLastPostDate($lastPostInForum->getPostDate());
                $forum->setLastPostPoster($lastPostInForum->getPoster());
                $forum->setLastPostTopic($lastPostInForum->getTopic());
            }
        }

        $em->flush();
    }
}

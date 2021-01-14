<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Forum;

use App\Domain\Entity\Forum as ForumEntity;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TopicMapper
 *
 * @package LaDanse\ForumBundle\Controller
 */
class TopicMapper
{
    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Topic $topic
     *
     * @return object
     */
    public function mapTopic(UrlGeneratorInterface $generator, ForumEntity\Topic $topic)
    {
        return (object)[
            'topicId' => $topic->getId(),
            'creatorId' => $topic->getCreator()->getId(),
            'creator' => $topic->getCreator()->getDisplayName(),
            'subject' => $topic->getSubject(),
            'createDate' => $topic->getCreateDate()->format(DateTime::ATOM),
            'lastPost' => $this->createLastPost($topic),
            'links' => (object)[
                'self'
                    => $generator->generate('getPostsInTopic', ['topicId' => $topic->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'createPostInTopic'
                    => $generator->generate('createPostInTopic', ['topicId' => $topic->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]
        ];
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Topic $topic
     *
     * @return object
     */
    public function mapTopicAndForum(UrlGeneratorInterface $generator, ForumEntity\Topic $topic)
    {
        $jsonTopic = $this->mapTopic($generator, $topic);

        $forumMapper = new ForumMapper();
        $jsonForum = $forumMapper->mapForum($generator, $topic->getForum());

        $jsonTopic->forum = $jsonForum;

        return $jsonTopic;
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param array $topics
     *
     * @return array
     */
    public function mapTopicsAndForum(UrlGeneratorInterface $generator, $topics)
    {
        $jsonTopics = [];

        /** @var ForumEntity\Topic $topic */
        foreach($topics as $topic)
        {
            $jsonTopics[] = $this->mapTopicAndForum($generator, $topic);
        }

        return $jsonTopics;
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Topic $topic
     *
     * @return object
     */
    public function mapTopicAndPosts(UrlGeneratorInterface $generator, ForumEntity\Topic $topic)
    {
        $topicObject = $this->mapTopic($generator, $topic);

        $posts = $topic->getPosts()->getValues();

        usort(
            $posts,
            function ($a, $b) {
                /** @var $a ForumEntity\Post */
                /** @var $b ForumEntity\Post */

                return $a->getPostDate() > $b->getPostDate();
            }
        );

        $postMapper = new PostMapper();

        $jsonArray = [];

        foreach ($posts as $post)
        {
            $jsonArray[] = $postMapper->mapPost($generator, $post);
        }

        $topicObject->posts = $jsonArray;

        return $topicObject;
    }

    private function createLastPost(ForumEntity\Topic $topic)
    {
        if ($topic->getLastPostPoster() !== null)
        {
            return (object)[
                'date' => $topic->getLastPostDate()->format(DateTime::ATOM),
                'poster' => (object)[
                    'id' => $topic->getLastPostPoster()->getId(),
                    'displayName' => $topic->getLastPostPoster()->getDisplayName()
                ]
            ];
        }

        return null;
    }
} 
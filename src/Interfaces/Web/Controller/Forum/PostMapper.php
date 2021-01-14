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
 * Class PostMapper
 *
 * @package LaDanse\ForumBundle\Controller
 */
class PostMapper
{
    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Post $post
     *
     * @return object
     */
    public function mapPost(UrlGeneratorInterface $generator, ForumEntity\Post $post)
    {
        return (object)[
            'postId' => $post->getId(),
            'posterId' => $post->getPoster()->getId(),
            'poster' => $post->getPoster()->getDisplayName(),
            'message' => $post->getMessage(),
            'postDate' => $post->getPostDate()->format(DateTime::ATOM),
            'links' => (object)[
                'self' => $generator->generate('getPost', ['postId' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'update' => $generator->generate('updatePost', ['postId' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]
        ];
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Post $post
     *
     * @return object
     */
    public function mapPostAndTopic(UrlGeneratorInterface $generator, ForumEntity\Post $post)
    {
        $jsonPost = $this->mapPost($generator, $post);

        $topicMapper = new TopicMapper();
        $jsonForum = $topicMapper->mapTopicAndForum($generator, $post->getTopic());

        $jsonPost->topic = $jsonForum;

        return $jsonPost;
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param array $posts
     *
     * @return array
     */
    public function mapPostsAndTopic(UrlGeneratorInterface $generator, $posts)
    {
        $jsonPosts = [];

        /** @var ForumEntity\Post $post */
        foreach($posts as $post)
        {
            $jsonPosts[] = $this->mapPostAndTopic($generator, $post);
        }

        return $jsonPosts;
    }
} 
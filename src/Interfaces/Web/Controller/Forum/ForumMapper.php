<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Forum;

use App\Domain\Entity\Forum as ForumEntity;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ForumMapper
{
    public function mapForums(UrlGeneratorInterface $generator, $forums)
    {
        $jsonForums = [];

        /** @var ForumEntity\Forum $forum */
        foreach($forums as $forum)
        {
            $jsonForums[] = $this->mapForum($generator, $forum);
        }

        return (object)[
            "forums"  => $jsonForums,
            "links"   => (object)[
                "self"        => $generator->generate('getForumList', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ]
        ];
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Forum $forum
     *
     * @return object
     */
    public function mapForum(UrlGeneratorInterface $generator, ForumEntity\Forum $forum)
    {
        return (object)[
            "forumId"        => $forum->getId(),
            "name"           => $forum->getName(),
            "description"    => $forum->getDescription(),
            "lastPost"       => $this->createLastPost($forum),
            "links"          => (object)[
                "self"        => $generator->generate('getForum', ['forumId' => $forum->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                "createTopic" => $generator->generate('createTopic', ['forumId' => $forum->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]
        ];
    }

    /**
     * @param UrlGeneratorInterface $generator
     * @param ForumEntity\Forum $forum
     *
     * @return object
     */
    public function mapForumAndTopics(UrlGeneratorInterface $generator, ForumEntity\Forum $forum)
    {
        $topics = $forum->getTopics()->getValues();

        usort(
            $topics,
            function ($a, $b)
            {
                /** @var ForumEntity\Topic $a */
                /** @var ForumEntity\Topic $b */
                return $a->getLastPostDate() < $b->getLastPostDate();
            }
        );

        $topicMapper = new TopicMapper();

        $jsonArray = [];

        foreach ($topics as $topic)
        {
            $jsonArray[] = $topicMapper->mapTopic($generator, $topic);
        }

        $jsonForum = $this->mapForum($generator, $forum);

        $jsonForum->topics = $jsonArray;

        return $jsonForum;
    }

    private function createLastPost(ForumEntity\Forum $forum)
    {
        if ($forum->getLastPostPoster() !== null)
        {
            return (object)[
                'date' => $forum->getLastPostDate()->format(DateTime::ATOM),
                'topic' => (object)[
                    'id' => $forum->getLastPostTopic()->getId(),
                    'subject' => $forum->getLastPostTopic()->getSubject()
                ],
                'poster' => (object)[
                    'id' => $forum->getLastPostPoster()->getId(),
                    'displayName' => $forum->getLastPostPoster()->getDisplayName()
                ]
            ];
        }

        return null;
    }
} 
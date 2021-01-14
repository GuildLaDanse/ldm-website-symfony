<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Forum\DTO;

use App\Domain\Entity\Forum as ForumEntity;
use App\Core\Modules\Common\AccountReference;

class ForumFactory
{
    public static function create(ForumEntity\Forum $forum): Forum
    {
        $factory = new self();

        return $factory->createForum($forum);
    }

    private function createForum(ForumEntity\Forum $forum): Forum
    {
        return new Forum(
            $forum->getId(),
            $forum->getName(),
            $forum->getDescription(),
            $this->createTopicEntries($forum));
    }

    private Function createTopicEntries(ForumEntity\Forum $forum)
    {
        $topicEntries = [];

        /** @var ForumEntity\Topic $topic */
        foreach($forum->getTopics() as $topic)
        {
            $topicEntries[] = new TopicEntry(
                $topic->getId(),
                $topic->getSubject(),
                $topic->getCreateDate(),
                new AccountReference(
                    $topic->getCreator()->getId(),
                    $topic->getCreator()->getDisplayName()),
                new LastPostEntry(
                    $topic->getLastPostDate(),
                    new AccountReference(
                        $topic->getLastPostPoster()->getId(),
                        $topic->getLastPostPoster()->getDisplayName())
                ));
        }

        return $topicEntries;
    }
}
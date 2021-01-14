<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Core\Modules\Comment;

use App\Domain\Entity\Comments\Comment;
use App\Domain\Entity\Comments\CommentGroup;
use App\Infrastructure\Modules\UUIDUtils;
use App\Core\Modules\Common\BadRequestException;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class CommentService
{
    /**
     * @var ManagerRegistry
     */
    private ManagerRegistry $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param $groupId
     *
     * @return CommentGroup
     *
     * @throws CommentGroupDoesNotExistException
     */
    public function getCommentGroup($groupId): CommentGroup
    {
        $groupRepo = $this->doctrine->getRepository(CommentGroup::class);

        /** @var CommentGroup $group */
        $group = $groupRepo->find($groupId);

        if (null === $group)
        {
            throw new CommentGroupDoesNotExistException('CommentGroup does not exist: ' . $groupId);
        }

        return $group;
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function createCommentGroup(): string
    {
       $em = $this->doctrine->getManager();

        $groupId = UUIDUtils::createUUID();
        
        $group = new CommentGroup();

        $group->setId($groupId);
        $group->setCreateDate(new DateTime());

        $em->persist($group);

        return $groupId;
    }

    /**
     * @param $groupId
     *
     * @throws CommentGroupDoesNotExistException
     */
    public function removeCommentGroup($groupId)
    {
        $em = $this->doctrine->getManager();

        $groupRepo = $this->doctrine->getRepository(CommentGroup::class);

        $group = $groupRepo->find($groupId);

        if (null === $group)
        {
            throw new CommentGroupDoesNotExistException("CommentGroup does not exist: " . $groupId);
        }
        else
        {
            $em->remove($group);
        }
    }

    /**
     * @param $commentId
     *
     * @return Comment
     *
     * @throws CommentDoesNotExistException
     */
    public function getComment($commentId)
    {
        $commentRepo = $this->doctrine->getRepository(Comment::class);

        /** @var Comment $comment */
        $comment = $commentRepo->find($commentId);

        if (null === $comment)
        {
            throw new CommentDoesNotExistException("Comment does not exist: " . $commentId);
        }
        else
        {
            return $comment;
        }
    }

    /**
     * @param $groupId
     * @param $account
     * @param $message
     *
     * @throws CommentGroupDoesNotExistException
     * @throws BadRequestException
     */
    public function createComment($groupId, $account, $message): void
    {
        $em = $this->doctrine->getManager();
        $groupRepo = $this->doctrine->getRepository(CommentGroup::class);

        /** @var CommentGroup $group */
        $group = $groupRepo->find($groupId);

        if (null === $group)
        {
            throw new CommentGroupDoesNotExistException('CommentGroup does not exist: ' . $groupId);
        }

        if ($message === null || '' === $message)
        {
            throw new BadRequestException('Message cannot be empty or null');
        }

        if (strlen($message) > 250)
        {
            throw new BadRequestException('Message cannot be larger than 250 characters');
        }

        $comment = new Comment();

        $comment->setId(UUIDUtils::createUUID());
        $comment->setPostDate(new DateTime());
        $comment->setPoster($account);
        $comment->setMessage($message);
        $comment->setGroup($group);

        $group->addComment($comment);

        $em->persist($comment);
    }

    /**
     * @param $commentId
     * @param $message
     *
     * @throws CommentDoesNotExistException
     * @throws BadRequestException
     */
    public function updateComment($commentId, $message): void
    {
        $em = $this->doctrine->getManager();
        $commentRepo = $this->doctrine->getRepository(Comment::class);

        $comment = $commentRepo->find($commentId);

        if (null === $comment)
        {
            throw new CommentDoesNotExistException('Post does not exist: ' . $commentId);
        }

        if ($message === null || '' === $message)
        {
            throw new BadRequestException('Message cannot be empty or null');
        }

        if (strlen($message) > 250)
        {
            throw new BadRequestException('Message cannot be larger than 250 characters');
        }

        $comment->setMessage($message);

        $em->persist($comment);
    }
}

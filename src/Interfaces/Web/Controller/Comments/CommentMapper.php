<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Comments;

use App\Domain\Entity\Comments\Comment;
use DateTime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class CommentMapper
 *
 * @package LaDanse\ForumBundle\Controller
 */
class CommentMapper
{
    /**
     * @param UrlGeneratorInterface $generator
     * @param Comment $comment
     *
     * @return object
     */
    public function mapComment(UrlGeneratorInterface $generator, Comment $comment)
    {
        return (object)[
            'postId' => $comment->getId(),
            'posterId' => $comment->getPoster()->getId(),
            'poster' => $comment->getPoster()->getDisplayName(),
            'message' => $comment->getMessage(),
            'postDate' => $comment->getPostDate()->format(DateTime::ATOM),
            'links' => (object)[
                'update' => $generator->generate('updateComment', ['commentId' => $comment->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]
        ];
    }
} 
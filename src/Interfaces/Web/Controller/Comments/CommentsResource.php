<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Comments;

use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\ParameterUtils;
use App\Infrastructure\Rest\ResourceHelper;
use App\Infrastructure\Security\AuthenticationService;
use App\Core\Modules\Comment\CommentDoesNotExistException;
use App\Core\Modules\Comment\CommentGroupDoesNotExistException;
use App\Core\Modules\Comment\CommentService;
use App\Core\Modules\Common\BadRequestException;
use JsonException;
use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api/comments")
 */
class CommentsResource extends AbstractRestController
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    public function  __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param CommentService $commentService
     * @param string $groupId
     *
     * @return Response
     *
     * @Route("/groups/{groupId}", name="getCommentsInGroup", methods={"GET"})
     *
     * @throws ServiceException
     */
    public function getCommentsInGroupAction(
        Request $request,
        CommentService $commentService,
        string $groupId): Response
    {
        ParameterUtils::isGuidOrThrow($groupId, 'groupId');

        try
        {
            $group = $commentService->getCommentGroup($groupId);
        }
        catch (CommentGroupDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ['Allow' => 'GET']
            );
        }

        $groupMapper = new CommentGroupMapper();

        $jsonObject = $groupMapper->mapGroupAndComments($this->get('router'), $group);

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param AuthenticationService $authenticationService
     * @param CommentService $commentService
     * @param string $groupId
     *
     * @return Response
     *
     * @throws ServiceException|JsonException
     *
     * @Route("/groups/{groupId}/comments", name="createComment", methods={"POST", "PUT"})
     */
    public function createCommentAction(
        Request $request,
        AuthenticationService $authenticationService,
        CommentService $commentService,
        $groupId): Response
    {
        ParameterUtils::isGuidOrThrow($groupId, 'groupId');

        $authContext = $authenticationService->getCurrentContext();

        if (!$authContext->isAuthenticated())
        {
            $this->logger->warning(__CLASS__ . ' the user was not authenticated in createComment');

            $jsonObject = (object)[
                'status' => 'must be authenticated'
            ];

            return new JsonResponse($jsonObject);
        }

        $jsonData = $request->getContent(false);

        $this->logger->info('Got jsonData ' . $jsonData);

        $jsonObject = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        try
        {
            $commentService->createComment($groupId, $authContext->getAccount(), $jsonObject['message']);
        }
        catch (CommentGroupDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ['Allow' => 'GET']
            );
        }
        catch (BadRequestException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_BAD_REQUEST,
                $e->getMessage(),
                ['Allow' => 'GET']
            );
        }

        $jsonObject = (object)[
            'status' => 'comment created in group'
        ];

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param CommentService $commentService
     * @param AuthenticationService $authenticationService
     * @param string $commentId
     *
     * @return Response
     *
     * @Route("/comments/{commentId}", name="updateComment", methods={"POST", "PUT"})
     */
    public function updateCommentAction(
        Request $request,
        CommentService $commentService,
        AuthenticationService $authenticationService,
        $commentId): Response
    {
        $authContext = $authenticationService->getCurrentContext();

        $comment = null;

        try
        {
            $comment = $commentService->getComment($commentId);
        }
        catch (CommentDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ['Allow' => 'GET']
            );
        }

        if (!($comment->getPoster()->getId() === $authContext->getAccount()->getId()))
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_FORBIDDEN,
                'Not allowed',
                ['Allow' => 'GET']
            );
        }

        $jsonData = $request->getContent(false);

        /** @noinspection PhpUnhandledExceptionInspection */
        $jsonObject = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        try
        {
            $commentService->updateComment($commentId, $jsonObject['message']);
        }
        catch (CommentDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ['Allow' => 'GET']
            );
        }
        catch (BadRequestException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_BAD_REQUEST,
                $e->getMessage(),
                ['Allow' => 'GET']
            );
        }

        $jsonObject = (object)[
            'status' => 'OK'
        ];

        return new JsonResponse($jsonObject);
    }
}

<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Forum;

use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\ResourceHelper;
use App\Infrastructure\Security\AuthenticationService;
use App\Core\Modules\Forum\ForumService;
use App\Core\Modules\Forum\ForumStatsService;
use App\Core\Modules\Forum\PostDoesNotExistException;
use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/posts")
 */
class PostsResource extends AbstractRestController
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
     * @param ForumService $forumService
     * @param string $postId
     *
     * @return Response
     *
     * @Route("/{postId}", name="getPost", methods={"GET"})
     */
    public function getPostAction(
        Request $request,
        ForumService $forumService,
        $postId)
    {
        try
        {
            $post = $forumService->getPost($postId);
        }
        catch (PostDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        $postMapper = new PostMapper();

        $jsonObject = $postMapper->mapPost($this->get('router'), $post);

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param AuthenticationService $authenticationService
     * @param ForumService $forumService
     * @param string $postId
     *
     * @return Response
     *
     * @Route("/{postId}", name="updatePost", methods={"POST", "PUT"})
     */
    public function updatePostAction(
        Request $request,
        AuthenticationService $authenticationService,
        ForumService $forumService,
        $postId)
    {
        $authContext = $authenticationService->getCurrentContext();

        $post = null;

        try
        {
            $post = $forumService->getPost($postId);
        }
        catch (PostDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        if (!($post->getPoster()->getId() == $authContext->getAccount()->getId()))
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_FORBIDDEN,
                'Not allowed',
                ["Allow" => "GET"]
            );
        }

        $jsonData = $request->getContent(false);

        $jsonObject = json_decode($jsonData);

        try
        {
            $forumService->updatePost(
                $authContext->getAccount(),
                $postId,
                $jsonObject->message);

            $jsonObject = (object)[
                "posts" => "test"
            ];

            return new JsonResponse($jsonObject);
        }
        catch (PostDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }
    }

    /**
     * @param AuthenticationService $authenticationService
     * @param ForumStatsService $statsService
     * @param string $postId
     *
     * @return Response
     *
     * @Route("/{postId}/markRead", name="markPostAsRead", methods={"GET", "POST", "PUT"})
     */
    public function markPostAsReadAction(
        AuthenticationService $authenticationService,
        ForumStatsService $statsService,
        $postId)
    {
        $authContext = $authenticationService->getCurrentContext();

        if (!$authContext->isAuthenticated())
        {
            $this->logger->warning(__CLASS__ . ' the user was not authenticated in markPostAsRead');

            $jsonObject = (object)[
                "status" => "must be authenticated"
            ];

            return new JsonResponse($jsonObject);
        }

        $account = $authContext->getAccount();

        $statsService->markPostAsRead($account, $postId);

        $jsonObject = (object)[
            "status" => "200"
        ];

        return new JsonResponse($jsonObject);
    }
}

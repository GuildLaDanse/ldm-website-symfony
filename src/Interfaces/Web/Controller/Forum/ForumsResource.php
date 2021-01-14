<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Forum;

use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\ResourceHelper;
use App\Infrastructure\Security\AuthenticationService;
use App\Core\Modules\Forum\ForumDoesNotExistException;
use App\Core\Modules\Forum\ForumService;
use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ForumsResource
 *
 * @package LaDanse\ForumBundle\Controller
 *
 * @Route("/forums")
 */
class ForumsResource extends AbstractRestController
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
     * @param ForumService $forumService
     *
     * @return Response
     *
     * @Route("/", name="getForumList", methods={"GET"})
     */
    public function getForumListAction(ForumService $forumService)
    {
        $forums = $forumService->getAllForums();

        $forumMapper = new ForumMapper();

        $jsonObject = $forumMapper->mapForums($this->get('router'), $forums);

        return new JsonResponse($jsonObject);
    }

    /**
     * @param ForumService $forumService
     *
     * @return Response
     *
     * @Route("/activity", name="getActivityForForums", methods={"GET"})
     */
    public function getActivityForForumsAction(ForumService $forumService)
    {
        $posts = $forumService->getActivityForForums();

        $postMapper = new PostMapper();

        $jsonObject = (object)[
            "posts"   => $postMapper->mapPostsAndTopic($this->get('router'), $posts),
            "links"   => (object)[
                "self"  => $this->generateUrl('getActivityForForums', [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ]
        ];

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param ForumService $forumService
     * @param string $forumId
     *
     * @return Response
     *
     * @Route("/{forumId}", name="getForum", methods={"GET"})
     */
    public function getForumForIdAction(
        Request $request,
        ForumService $forumService,
        $forumId)
    {
        try
        {
            $forum = $forumService->getForum($forumId);
        }
        catch (ForumDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        $forumMapper = new ForumMapper();

        $jsonObject = $forumMapper->mapForumAndTopics($this->get('router'), $forum);

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param ForumService $forumService
     * @param string $forumId
     *
     * @return Response
     *
     * @Route("/{forumId}/activity", name="getActivityForForum", methods={"GET"})
     */
    public function getActivityForForumAction(
        Request $request,
        ForumService $forumService,
        $forumId)
    {
        try
        {
            $forumService->getForum($forumId);
        }
        catch (ForumDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        $posts = $forumService->getActivityForForum($forumId);

        $postMapper = new PostMapper();

        $jsonObject = (object)[
            "posts"   => $postMapper->mapPostsAndTopic($this->get('router'), $posts),
            "links"   => (object)[
                "self"  => $this->generateUrl('getActivityForForum', ['forumId' => $forumId], UrlGeneratorInterface::ABSOLUTE_PATH)
            ]
        ];

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param ForumService $forumService
     * @param AuthenticationService $authenticationService
     * @param string $forumId
     *
     * @return Response
     *
     * @Route("/{forumId}/topics", name="createTopic", methods={"POST", "PUT"})
     */
    public function createTopicAction(
        Request $request,
        ForumService $forumService,
        AuthenticationService $authenticationService,
        $forumId)
    {
        $authContext = $authenticationService->getCurrentContext();

        if (!$authContext->isAuthenticated())
        {
            $this->logger->warning(__CLASS__ . ' the user was not authenticated in calendarIndex');

            $jsonObject = (object)[
                "status" => "must be authenticated"
            ];

            return new JsonResponse($jsonObject);
        }

        $jsonData = $request->getContent(false);

        $jsonObject = json_decode($jsonData);

        try
        {
            $forumService->createTopicInForum(
                $authContext->getAccount(),
                $forumId,
                $jsonObject->subject,
                $jsonObject->text
            );
        }
        catch (ForumDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        $jsonObject = (object)[
            "status" => "topic created in forum"
        ];

        return new JsonResponse($jsonObject);
    }
}

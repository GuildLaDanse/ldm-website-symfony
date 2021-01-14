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
use App\Core\Modules\Forum\TopicDoesNotExistException;
use Psr\Log\LoggerInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/topics")
 */
class TopicsResource extends AbstractRestController
{
    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param ForumService $forumService
     * @param string $topicId
     *
     * @return Response
     *
     * @Route("/{topicId}", name="getPostsInTopic", methods={"GET"})
     */
    public function getTopicAction(
        Request $request,
        ForumService $forumService,
        $topicId)
    {
        try
        {
            $topic = $forumService->getTopic($topicId);
        }
        catch (TopicDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        $topicMapper = new TopicMapper();

        $jsonObject = $topicMapper->mapTopicAndPosts($this->get('router'), $topic);

        return new JsonResponse($jsonObject);
    }

    /**
     * @param Request $request
     * @param AuthenticationService $authenticationService
     * @param ForumService $forumService
     * @param string $topicId
     *
     * @return Response
     *
     * @Route("/{topicId}/posts", name="createPostInTopic", methods={"POST", "PUT"})
     */
    public function createPostInTopicAction(
        Request $request,
        AuthenticationService $authenticationService,
        ForumService $forumService,
        $topicId)
    {
        $authContext = $authenticationService->getCurrentContext();

        if (!$authContext->isAuthenticated())
        {
            $this->logger->warning(__CLASS__ . ' the user was not authenticated in createPost');

            $jsonObject = (object)[
                "status" => "must be authenticated"
            ];

            return new JsonResponse($jsonObject);
        }

        $jsonData = $request->getContent(false);

        $jsonObject = json_decode($jsonData);

        try
        {
            $forumService->createPost(
                $authContext->getAccount(),
                $topicId,
                $jsonObject->message);

            $jsonObject = (object)[
                "status" => "post created in topic"
            ];

            return new JsonResponse($jsonObject);
        }
        catch (TopicDoesNotExistException $e)
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
     * @param Request $request
     * @param AuthenticationService $authenticationService
     * @param ForumService $forumService
     * @param string $topicId
     *
     * @return Response
     *
     * @throws TopicDoesNotExistException
     *
     * @Route("/{topicId}", name="updateTopic", methods={"POST", "PUT"})
     */
    public function updateTopicAction(
        Request $request,
        AuthenticationService $authenticationService,
        ForumService $forumService,
        $topicId)
    {
        $authContext = $authenticationService->getCurrentContext();

        $topic = null;

        try
        {
            $topic = $forumService->getTopic($topicId);
        }
        catch (TopicDoesNotExistException $e)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                Response::HTTP_NOT_FOUND,
                $e->getMessage(),
                ["Allow" => "GET"]
            );
        }

        if (!($topic->getCreator()->getId() == $authContext->getAccount()->getId()))
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

        $forumService->updateTopic(
            $authContext->getAccount(),
            $topicId,
            $jsonObject->subject
        );

        $jsonObject = (object)[
            "posts" => "test"
        ];

        return new JsonResponse($jsonObject);
    }
}

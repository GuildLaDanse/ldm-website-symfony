<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Interfaces\Web\Controller\Event;

use App\Infrastructure\Modules\ServiceException;
use App\Infrastructure\Rest\AbstractRestController;
use App\Infrastructure\Rest\JsonSerializedResponse;
use App\Infrastructure\Rest\ParameterUtils;
use App\Infrastructure\Rest\ResourceHelper;
use App\Core\Modules\Event\DTO\PostEvent;
use App\Core\Modules\Event\DTO\PostSignUp;
use App\Core\Modules\Event\DTO\PutEvent;
use App\Core\Modules\Event\DTO\PutEventState;
use App\Core\Modules\Event\DTO\PutSignUp;
use App\Core\Modules\Event\EventService;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/events")
 */
class EventsResource extends AbstractRestController
{
    /**
     * @param Request $request
     * @param EventService $eventService
     *
     * @return Response
     *
     * @Route("/", name="queryEvents", options = { "expose" = true }, methods={"GET", "HEAD"})
     *
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public function queryEventsAction(Request $request, EventService $eventService): Response
    {
        try {
            /** @var DateTime $startOnDate */
            $startOnDate = $this->getStartOnDate($request->query->get('startOn'));

            $eventPage = $eventService->getAllEventsPaged($startOnDate);

            return new JsonSerializedResponse($eventPage);
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     *
     * @return Response
     *
     * @Route("/{eventId}", name="queryEventById", options = { "expose" = true }, methods={"GET", "HEAD"})
     *
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @throws ServiceException
     */
    public function queryEventByIdAction(Request $request, EventService $eventService, string $eventId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');

        try
        {
            $event = $eventService->getEventById((int)$eventId);

            return new JsonSerializedResponse($event);
        }
        catch (ServiceException $serviceException)
        {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     *
     * @return Response
     *
     * @Route("/", name="postEvent", options = { "expose" = true }, methods={"POST"})
     */
    public function postEventAction(Request $request, EventService $eventService): Response
    {
        try {
            /** @var PostEvent $postEventDto */
            $postEventDto = $this->getDtoFromContent($request, PostEvent::class);

            $eventDto = $eventService->postEvent($postEventDto);

            return new JsonSerializedResponse(ResourceHelper::object($eventDto));
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     *
     * @return Response
     *
     * @Route("/{eventId}", name="putEvent", options = { "expose" = true }, methods={"PUT"})
     *
     * @throws ServiceException
     */
    public function putEventAction(Request $request, EventService $eventService, string $eventId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');

        try {
            /** @var PutEvent $putEventDto */
            $putEventDto = $this->getDtoFromContent($request, PutEvent::class);

            $eventDto = $eventService->putEvent((int)$eventId, $putEventDto);

            return new JsonSerializedResponse(ResourceHelper::object($eventDto));
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     *
     * @return Response
     *
     * @Route("/{eventId}/state", name="putEventState", options = { "expose" = true }, methods={"PUT"})
     *
     * @throws ServiceException
     */
    public function putEventStateAction(Request $request, EventService $eventService, string $eventId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');

        try {
            /** @var PutEventState $putEventStateDto */
            $putEventStateDto = $this->getDtoFromContent($request, PutEventState::class);

            $eventDto = $eventService->putEventState((int)$eventId, $putEventStateDto);

            return new JsonSerializedResponse(ResourceHelper::object($eventDto));
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     *
     * @return Response
     *
     * @Route("/{eventId}", name="deleteEvent", options = { "expose" = true }, methods={"DELETE"})
     *
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @throws ServiceException
     */
    public function deleteEventAction(Request $request, EventService $eventService, string $eventId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');

        try {
            $eventService->deleteEvent((int)$eventId);

            return new Response();
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     *
     * @return Response
     *
     * @Route("/{eventId}/signUps", name="postSignUp", options = { "expose" = true }, methods={"POST"})
     *
     * @throws ServiceException
     */
    public function postSignUpAction(Request $request, EventService $eventService, string $eventId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');

        try {
            /** @var PostSignUp $postSignUpDto */
            $postSignUpDto = $this->getDtoFromContent($request, PostSignUp::class);

            $eventDto = $eventService->postSignUp((int)$eventId, $postSignUpDto);

            return new JsonSerializedResponse(ResourceHelper::object($eventDto));
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     * @param string $signUpId
     *
     * @return Response
     *
     * @Route("/{eventId}/signUps/{signUpId}", name="putSignUp", options = { "expose" = true }, methods={"PUT"})
     *
     * @throws ServiceException
     */
    public function putSignUpAction(Request $request, EventService $eventService, string $eventId, string $signUpId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');
        ParameterUtils::isIntegerOrThrow($signUpId, 'signUpId');

        try {
            /** @var PutSignUp $putSignUpDto */
            $putSignUpDto = $this->getDtoFromContent($request, PutSignUp::class);

            $eventDto = $eventService->putSignUp((int)$eventId, (int)$signUpId, $putSignUpDto);

            return new JsonSerializedResponse(ResourceHelper::object($eventDto));
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    /**
     * @param Request $request
     * @param EventService $eventService
     * @param string $eventId
     * @param string $signUpId
     *
     * @return Response
     *
     * @Route("/{eventId}/signUps/{signUpId}", name="deleteSignUp", options = { "expose" = true }, methods={"DELETE"})
     *
     * @noinspection PhpRedundantCatchClauseInspection
     *
     * @throws ServiceException
     */
    public function deleteSignUpAction(Request $request, EventService $eventService, string $eventId, string $signUpId): Response
    {
        ParameterUtils::isIntegerOrThrow($eventId, 'eventId');
        ParameterUtils::isIntegerOrThrow($signUpId, 'signUpId');

        try {
            $eventDto = $eventService->deleteSignUp((int)$eventId, (int)$signUpId);

            return new JsonSerializedResponse(ResourceHelper::object($eventDto));
        } catch (ServiceException $serviceException) {
            return ResourceHelper::createErrorResponse(
                $request,
                $serviceException->getCode(),
                $serviceException->getMessage()
            );
        }
    }

    private function getStartOnDate($pStartOnDate)
    {
        if ($pStartOnDate === null) {
            return new DateTime();
        }

        return DateTime::createFromFormat('Ymd', $pStartOnDate);
    }
}

<?php declare(strict_types=1);
/**
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/GuildLaDanse
 */

namespace App\Infrastructure\Rest;

use App\Infrastructure\Modules\ServiceException;
use Error;
use ErrorException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RestApiExceptionSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['handleException', 9999],
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        if (!$this->startsWith($event->getRequest()->getPathInfo(), '/api'))
        {
            return;
        }

        $this->logger->debug('Exception from /api, sending proper response');

        $throwable = $event->getThrowable();

        $this->logger->debug('Throwable: ' . get_class($throwable));
        $this->logger->debug('Status code: ' . $throwable->getCode());

        $statusCode = $throwable->getCode();
        $message = $throwable->getMessage();

        if ($throwable instanceof ServiceException)
        {
            /** @var ServiceException $serviceException */
            $serviceException = $throwable;

            $statusCode = $serviceException->getCode();
        }
        else if ($throwable instanceof Error || $throwable instanceof ErrorException)
        {
            $message = 'Internal Server Error';
        }
        else
        {
            return;
        }

        if ($statusCode === 0)
        {
            $statusCode = 500;
        }

        $event->allowCustomResponseCode();
        $event->setResponse(ResourceHelper::createErrorResponse(
            $event->getRequest(),
            $statusCode,
            $message
        ));
    }

    private function startsWith($string, $startString): bool
    {
        return (strpos($string, $startString) === 0);
    }
}
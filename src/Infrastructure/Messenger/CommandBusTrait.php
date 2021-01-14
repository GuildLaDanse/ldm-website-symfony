<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;


use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

trait CommandBusTrait
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $_commandBus;

    /**
     * @param $command
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public function dispatchCommand($command)
    {
        try
        {
            $envelope = $this->_commandBus->dispatch($command);

            $handledStamp = $envelope->last(HandledStamp::class);

            /** @noinspection NullPointerExceptionInspection */
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            return $handledStamp->getResult();
        }
        catch (HandlerFailedException $e)
        {
            while ($e instanceof HandlerFailedException)
            {
                /** @var Throwable $e */
                $e = $e->getPrevious();
            }

            throw $e;
        }
    }
}
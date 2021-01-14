<?php

declare(strict_types=1);

namespace App\Infrastructure\Messenger;


use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Throwable;

trait QueryBusTrait
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $_queryBus;

    /**
     * @param $query
     *
     * @return mixed
     *
     * @throws Throwable
     */
    public function dispatchQuery($query)
    {
        try
        {
            $envelope = $this->_commandBus->dispatch($query);

            $handledStamp = $envelope->last(HandledStamp::class);

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
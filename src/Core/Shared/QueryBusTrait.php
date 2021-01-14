<?php

declare(strict_types=1);

namespace App\Core\Shared;


use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

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
     */
    public function dispatchQuery($query)
    {
        $envelope = $this->_queryBus->dispatch($query);

        $handledStamp = $envelope->last(HandledStamp::class);

        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return $handledStamp->getResult();
    }
}
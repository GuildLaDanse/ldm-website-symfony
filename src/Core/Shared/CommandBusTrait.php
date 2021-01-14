<?php

declare(strict_types=1);

namespace App\Core\Shared;


use Symfony\Component\Messenger\MessageBusInterface;

trait CommandBusTrait
{
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $_commandBus;

    /**
     * @param $command
     */
    public function dispatchCommand($command): void
    {
        $this->_commandBus->dispatch($command);
    }
}
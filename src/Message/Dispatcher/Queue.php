<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Handler;

class Queue implements Handler
{
    private $handler;
    private $queue;
    private $isDispatching = false;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function handle(DomainMessage $message): void
    {
        $this->queue[] = $message;
        if ($this->isDispatching) {
            return;
        }

        $this->isDispatching = true;

        do {
            $next = array_shift($this->queue);
            $this->handler->handle($next);
        } while (!empty($this->queue));

        $this->isDispatching = false;
    }
}

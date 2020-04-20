<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Handler;

class All implements Handler
{
    private $handlers;

    public function __construct(Handler ...$handlers)
    {
        $this->handlers = $handlers;
    }

    public function handle(DomainMessage $message): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($message);
        }
    }
}

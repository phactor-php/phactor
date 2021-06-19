<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Handler;

class Capture implements Handler
{
    private Handler $handler;
    private array $messages = [];

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function handle(DomainMessage $message): void
    {
        $this->messages[] = $message;
        $this->handler->handle($message);
    }

    public function capturedMessages()
    {
        return $this->messages;
    }

    public function reset()
    {
        $this->messages = [];
    }
}

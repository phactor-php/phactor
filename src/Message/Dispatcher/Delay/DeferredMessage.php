<?php

namespace Phactor\Message\Dispatcher\Delay;

use Phactor\DomainMessage;

class DeferredMessage
{
    private string $id;
    private \DateTimeInterface $time;

    public function __construct(DomainMessage $message)
    {
        $this->id = $message->getId();
        $this->time = $message->getTime();
    }

    public function isDispatchable(): bool
    {
        return new \DateTime >= $this->time;
    }

    public function getId(): string
    {
        return $this->id;
    }
}

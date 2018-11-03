<?php

namespace Carnage\Phactor\Message\DelayedMessage;

use Carnage\Phactor\Message\DomainMessage;

class DeferredMessage
{
    private $id;
    private $time;

    public function __construct(DomainMessage $message)
    {
        $this->id = $message->getId();
        $this->time = \DateTimeImmutable::createFromMutable($message->getTime());
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
<?php

namespace Phactor\Actor\Subscription;

use Phactor\Actor\ActorIdentity;

final class Subscription
{
    private $listenerClass;
    private $listenerId;
    private $subscribedToClass;
    private $subscribedToId;

    public function __construct(ActorIdentity $listener, ActorIdentity $subscribedTo)
    {
        $this->listenerClass = $listener->getClass();
        $this->listenerId = $listener->getId();
        $this->subscribedToClass = $subscribedTo->getClass();
        $this->subscribedToId = $subscribedTo->getId();
    }

    public function getListener(): ActorIdentity
    {
        return new ActorIdentity($this->listenerClass, $this->listenerId);
    }

    public function getSubscribedTo(): ActorIdentity
    {
        return new ActorIdentity($this->subscribedToClass, $this->subscribedToId);
    }
}

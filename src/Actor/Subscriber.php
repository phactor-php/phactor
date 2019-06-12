<?php

namespace Phactor\Actor;

use Phactor\Message\ActorIdentity;

interface Subscriber
{
    public function subscribe(ActorIdentity $listener, ActorIdentity $subscribeTo): void;
    public function unsubscribe(ActorIdentity $listener, ActorIdentity $subscribedTo): void;
}
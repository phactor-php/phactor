<?php

namespace Phactor\Test;

use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\ActorIdentity;
use Phactor\Message\DomainMessage;
use Phactor\Message\MessageFirer;
use Phactor\Persistence\InMemoryEventStore;

class ActorTester
{
    private $messageTester;
    private $messageFirer;
    private $eventStore;
    private $actorIdentity;

    public function __construct(MessageTester $messageTester, MessageFirer $messageFirer, InMemoryEventStore $eventStore, ActorIdentity $actorIdentity)
    {
        $this->messageTester = $messageTester;
        $this->messageFirer = $messageFirer;
        $this->eventStore = $eventStore;
        $this->actorIdentity = $actorIdentity;
    }

    public function given(array $messages)
    {
        foreach ($messages as $message) {
            $domainMessage = $this->prepareMessage($message);
            $this->eventStore->save($this->actorIdentity, $domainMessage);
        }

        return $this;
    }

    public function when($message)
    {
        $this->messageFirer->fire($message);
        $this->expect($message);

        return $this;
    }

    public function expectNoMoreMessages()
    {
        $this->messageTester->expectNoMoreMessages();
    }

    public function expect($message)
    {
        $this->messageTester->expect($message);
    }

    public function getActorIdentity()
    {
        return $this->actorIdentity;
    }

    private function prepareMessage($messageOrDomainMessage)
    {
        if ($messageOrDomainMessage instanceof DomainMessage) {
            return $messageOrDomainMessage;
        }

        return  DomainMessage::anonMessage((new YouTubeStyleIdentityGenerator())->generateIdentity(), $messageOrDomainMessage);
    }
}
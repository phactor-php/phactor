<?php

namespace Carnage\Phactor\Persistence;

use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;

class InMemoryEventStore implements EventStore
{
    private $store;

    public function load(ActorIdentity $identity): array
    {
        if (!isset($this->store[$identity->getClass()][$identity->getId()])) {
            throw new NotFoundException();
        }

        return $this->store[$identity->getClass()][$identity->getId()];
    }

    public function save(ActorIdentity $identity, DomainMessage ...$messages)
    {
        foreach ($messages as $message) {
            $this->store[$identity->getClass()][$identity->getId()][] = $message;
        }
    }
}
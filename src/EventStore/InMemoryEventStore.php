<?php

namespace Phactor\EventStore;

use Phactor\Actor\ActorIdentity;
use Phactor\DomainMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class InMemoryEventStore implements EventStore
{
    private $store;
    private $events;

    public function load(ActorIdentity $identity): Iterable
    {
        if (!isset($this->store[$identity->getClass()][$identity->getId()])) {
            throw new NoEventsFound();
        }

        return $this->store[$identity->getClass()][$identity->getId()];
    }

    public function save(ActorIdentity $identity, DomainMessage ...$messages)
    {
        foreach ($messages as $message) {
            $this->store[$identity->getClass()][$identity->getId()][] = $message;
            $this->events[] = $message;
        }
    }

    public function eventsMatching(Criteria $criteria): Iterable
    {
        return (new ArrayCollection($this->events))->matching($criteria);
    }
}

<?php

namespace Carnage\Phactor\Persistence;

use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class InMemoryEventStore implements EventStore
{
    private $store;
    private $events;

    public function load(ActorIdentity $identity): Iterable
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
            $this->events[] = $message;
        }
    }

    public function eventsMatching(Criteria $criteria): Iterable
    {
        return (new ArrayCollection($this->events))->matching($criteria);
    }
}
<?php

namespace Phactor\EventStore;

use Phactor\Actor\ActorIdentity;
use Phactor\DomainMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use phpDocumentor\Reflection\Types\Iterable_;

class InMemoryEventStore implements EventStore, LoadsEvents
{
    private $store;
    private $events;

    public function load(ActorIdentity $identity): iterable
    {
        if (!isset($this->store[$identity->getClass()][$identity->getId()])) {
            throw new NoEventsFound();
        }

        return $this->store[$identity->getClass()][$identity->getId()];
    }

    public function save(ActorIdentity $identity, DomainMessage ...$messages): void
    {
        foreach ($messages as $message) {
            $this->store[$identity->getClass()][$identity->getId()][] = $message;
            $this->events[] = $message;
        }
    }

    public function eventsMatching(Criteria $criteria): iterable
    {
        return (new ArrayCollection($this->events))->matching($criteria);
    }

    public function loadEventsByIds(string ...$ids): iterable
    {
        return $this->eventsMatching((new Criteria())->where(Criteria::expr()->in('id', $ids)));
    }

    public function loadEventsByClasses(string ...$classes): iterable
    {
        return $this->eventsMatching(Criteria::create()->where(Criteria::expr()->in('messageClass', $classes)));
    }
}

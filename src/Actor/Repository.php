<?php

namespace Phactor\Actor;

use Phactor\Actor\Subscription\Subscriber;
use Phactor\EventStore\TakesSnapshots;
use Phactor\Identity\Generator;
use Phactor\Message\Handler;
use Phactor\EventStore\EventStore;

class Repository
{
    private Handler $messageBus;
    private EventStore $eventStore;
    private Generator $generator;
    private Subscriber $subscriber;

    public function __construct(Handler $messageBus, EventStore $eventStore, Generator $generator, Subscriber $subscriber)
    {
        $this->messageBus = $messageBus;
        $this->eventStore = $eventStore;
        $this->generator = $generator;
        $this->subscriber = $subscriber;
    }

    public function save(ActorInterface $actor): void
    {
        $messages = $actor->newHistory();
        $this->eventStore->save(ActorIdentity::fromActor($actor), ...$messages);

        if ($actor->shouldSnapshot() && $this->eventStore instanceof TakesSnapshots) {
            $this->eventStore->saveSnapshot(ActorIdentity::fromActor($actor), $actor->getVersion(), $actor->snapshot());
        }

        foreach ($actor->publishableMessages() as $message) {
            $this->messageBus->handle($message);
        }

        $actor->committed();
    }

    public function load(ActorIdentity $actorIdentity): ActorInterface
    {
        $className = $actorIdentity->getClass();

        if ($this->eventStore instanceof TakesSnapshots && $this->eventStore->hasSnapshot($actorIdentity)) {
            $snapshot = $this->eventStore->loadSnapshot($actorIdentity);
            $messages = $this->eventStore->loadFromLastSnapshot($actorIdentity);

            /** @var ActorInterface $className */
            return $className::fromSnapshot($snapshot, $this->generator, $this->subscriber, ...$messages);
        }

        $messages = $this->eventStore->load($actorIdentity);

        /** @var ActorInterface $className */
        return $className::fromHistory($this->generator, $this->subscriber, $actorIdentity->getId(), ...$messages);
    }

    public function create($actorClass): ActorInterface
    {
        return new $actorClass($this->generator, $this->subscriber);
    }
}

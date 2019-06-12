<?php

namespace Phactor\Persistence;

use Phactor\Actor\ActorInterface;
use Phactor\Actor\Subscriber;
use Phactor\Identity\Generator;
use Phactor\Message\ActorIdentity;
use Phactor\Message\Bus;

class ActorRepository
{
    private $messageBus;
    private $eventStore;
    private $generator;
    private $subscriber;

    public function __construct(Bus $messageBus, EventStore $eventStore, Generator $generator, Subscriber $subscriber)
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

        foreach ($actor->publishableMessages() as $message) {
            $this->messageBus->handle($message);
        }

        $actor->committed();
    }

    public function load(ActorIdentity $actorIdentity): ActorInterface
    {
        $messages = $this->eventStore->load($actorIdentity);
        $className = $actorIdentity->getClass();
        /** @var ActorInterface $className */
        return $className::fromHistory($this->generator, $this->subscriber, $actorIdentity->getId(), ...$messages);
    }

    public function create($actorClass): ActorInterface
    {
        return new $actorClass($this->generator, $this->subscriber);
    }
}
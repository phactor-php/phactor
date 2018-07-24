<?php

namespace Carnage\Phactor\Persistence;

use Carnage\Phactor\Actor\ActorInterface;
use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\Bus;

class ActorRepository
{
    private $messageBus;
    private $eventStore;
    private $generator;

    public function __construct(Bus $messageBus, EventStore $eventStore, Generator $generator)
    {
        $this->messageBus = $messageBus;
        $this->eventStore = $eventStore;
        $this->generator = $generator;
    }

    public function save(ActorInterface $actor): void
    {
        $messages = $actor->newMessages();
        $this->eventStore->save(ActorIdentity::fromActor($actor), ...$messages);
        $actor->committed();

        foreach ($messages as $message) {
            $this->messageBus->handle($message);
        }
    }

    public function load(ActorIdentity $actorIdentity): ActorInterface
    {
        $messages = $this->eventStore->load($actorIdentity);
        $className = $actorIdentity->getClass();
        /** @var ActorInterface $className */
        return $className::fromHistory($this->generator, $actorIdentity->getId(), ...$messages);
    }

    public function create($actorClass): ActorInterface
    {
        return new $actorClass($this->generator);
    }
}
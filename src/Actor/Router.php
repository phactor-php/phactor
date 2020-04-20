<?php

namespace Phactor\Actor;

use Phactor\DomainMessage;
use Phactor\Message\Handler;

class Router implements Handler
{
    private $actorClass;
    private $actorRepository;

    public function __construct(string $actorClass, Repository $actorRepository)
    {
        if (!in_array(ActorInterface::class, class_implements($actorClass))) {
            throw new \RuntimeException('Actor must implement ActorInterface');
        }

        $this->actorRepository = $actorRepository;
        $this->actorClass = $actorClass;
    }

    public function handle(DomainMessage $message): void
    {
        $identity = $this->actorClass::extractId($message);
        if ($identity !== null) {
            $actor = $this->actorRepository->load(new ActorIdentity($this->actorClass, $identity));
        } else {
            $actor = $this->actorRepository->create($this->actorClass);
        }

        $actor->handle($message);

        $this->actorRepository->save($actor);
    }
}

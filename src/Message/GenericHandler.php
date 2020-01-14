<?php

namespace Phactor\Message;

use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\NotFoundException;

class GenericHandler implements Handler
{
    private $actorClass;
    private $actorRepository;

    public function __construct(string $actorClass, ActorRepository $actorRepository)
    {
        $this->actorClass = $actorClass;
        $this->actorRepository = $actorRepository;
    }

    public function handle(DomainMessage $message)
    {
        $identity = $this->actorClass::generateId($message);
        if ($identity !== null) {
            $actor = $this->actorRepository->load(new ActorIdentity($this->actorClass, $identity));
        } else {
            $actor = $this->actorRepository->create($this->actorClass);
        }

        $actor->handle($message);

        $this->actorRepository->save($actor);
    }
}
<?php

namespace Carnage\Phactor\Message;

use Carnage\Phactor\Persistence\ActorRepository;

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
        if ($message->getMessage() instanceof HasActorId) {
            $identity = $message->getMessage()->getActorId();
            $actor = $this->actorRepository->load(new ActorIdentity($this->actorClass, $identity));
        } else {
            $actor = $this->actorRepository->create($this->actorClass);
        }

        $actor->handle($message);

        $this->actorRepository->save($actor);
    }
}
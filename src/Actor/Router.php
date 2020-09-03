<?php

namespace Phactor\Actor;

use Phactor\DomainMessage;
use Phactor\Message\Handler;

class Router implements Handler
{
    private $actorClasses;
    private $actorRepository;

    public function __construct(Repository $actorRepository, string ...$actorClasses)
    {
        foreach ($actorClasses as $actorClass) {
            if (!in_array(ActorInterface::class, class_implements($actorClass))) {
                throw new \RuntimeException('Actor must implement ActorInterface');
            }
        }

        $this->actorRepository = $actorRepository;
        $this->actorClasses = $actorClasses;
    }

    public function handle(DomainMessage $domainMessage): void
    {
        $messageClass = get_class($domainMessage->getMessage());
        foreach ($this->actorClasses as $actorClass) {
            $subscriptions = $actorClass::getSubscriptions();
            if (in_array($messageClass, $subscriptions)) {
                $identity = $actorClass::extractId($domainMessage);
                if ($identity !== null) {
                    $actor = $this->actorRepository->load(new ActorIdentity($actorClass, $identity));
                } else {
                    $actor = $this->actorRepository->create($actorClass);
                }

                $actor->handle($domainMessage);

                $this->actorRepository->save($actor);
            }
        }
    }
}

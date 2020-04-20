<?php

namespace Phactor\Actor\Subscription;

use Doctrine\Common\Collections\Criteria;
use Phactor\Actor\ActorIdentity;
use Phactor\Actor\Repository as ActorRepository;
use Phactor\DomainMessage;
use Phactor\Message\Handler;
use Phactor\ReadModel\Repository as SubscriptionRepository;

final class SubscriptionHandler implements Handler
{
    private $repository;
    private $actorRepository;

    public function __construct(SubscriptionRepository $repository, ActorRepository $actorRepository)
    {
        $this->repository = $repository;
        $this->actorRepository = $actorRepository;
    }

    public function handle(DomainMessage $message): void
    {
        $producer = $message->getProducer();
        if (!($producer instanceof ActorIdentity)) {
            return;
        }

        $subscriptions = $this->repository->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('subscribedToClass', $producer->getClass()))
                ->andWhere(Criteria::expr()->eq('subscribedToId', $producer->getId()))
        );

        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            $actor = $this->actorRepository->load($subscription->getListener());
            $actor->handle($message);
            $this->actorRepository->save($actor);
        }
    }
}

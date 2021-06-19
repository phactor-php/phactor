<?php

namespace Phactor\Actor\Subscription;

use Doctrine\Common\Collections\Criteria;
use Phactor\Actor\ActorIdentity;
use Phactor\ReadModel\Repository;

final class Subscriber
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function subscribe(ActorIdentity $listener, ActorIdentity $subscribeTo): void
    {
        $this->repository->add(new Subscription($listener, $subscribeTo));
        $this->repository->commit();
    }

    public function unsubscribe(ActorIdentity $listener, ActorIdentity $subscribedTo): void
    {
        $subscription = $this->repository->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('listenerClass', $listener->getClass()))
                ->andWhere(Criteria::expr()->eq('listenerId', $listener->getId()))
                ->andwhere(Criteria::expr()->eq('subscribedToClass', $subscribedTo->getClass()))
                ->andWhere(Criteria::expr()->eq('subscribedToId', $subscribedTo->getId()))
        )->current();

        if ($subscription !== null) {
            $this->repository->remove($subscription);
        }
    }
}

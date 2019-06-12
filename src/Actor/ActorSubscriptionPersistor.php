<?php

namespace Phactor\Actor;

use Doctrine\Common\Collections\Criteria;
use Phactor\Message\ActorIdentity;
use Phactor\ReadModel\Repository;

class ActorSubscriptionPersistor implements Subscriber
{
    private $repository;

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
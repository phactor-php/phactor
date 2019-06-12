<?php


namespace Phactor\Actor;


use Doctrine\Common\Collections\Criteria;
use Phactor\Message\ActorIdentity;
use Phactor\Message\DomainMessage;
use Phactor\Message\Handler;
use Phactor\Persistence\ActorRepository;
use Phactor\ReadModel\Repository;

class ActorSubscriptionHandler implements Handler
{
    private $repository;
    private $actorRepository;

    public function __construct(Repository $repository, ActorRepository $actorRepository)
    {
        $this->repository = $repository;
        $this->actorRepository = $actorRepository;
    }

    public function handle(DomainMessage $message)
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
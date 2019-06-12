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
}
<?php

namespace Phactor\Message\DelayedMessage;

use Phactor\Message\Bus;
use Phactor\Message\DomainMessage;
use Phactor\Message\Handler;
use Phactor\Persistence\EventStore;
use Phactor\ReadModel\Repository;
use Doctrine\Common\Collections\Criteria;

class DelayedMessageBus implements Bus
{
    private $wrappedBus;
    private $repository;
    private $eventStore;

    public function __construct(Bus $wrappedBus, Repository $repository, EventStore $eventStore)
    {
        $this->wrappedBus = $wrappedBus;
        $this->repository = $repository;
        $this->eventStore = $eventStore;
    }

    public function handle(DomainMessage $message): void
    {
        if ($message->isInFuture()) {
            $this->repository->add(new DeferredMessage($message));
            $this->repository->commit();
            return;
        }

        $this->wrappedBus->handle($message);
    }

    public function subscribe(string $identifier, Handler $handler): void
    {
        $this->wrappedBus->subscribe($identifier, $handler);
    }

    public function processMessages()
    {
        $messages = $this->repository->matching(new Criteria());
        foreach ($messages as $message) {
            /** @var DeferredMessage $message */
            if (!$message->isDispatchable()) {
                continue;
            }

            $messages = $this->eventStore->eventsMatching((new Criteria())->where(Criteria::expr()->eq('id', $message->getId())));
            //should only be one at this stage as it's not been dispatched
            $domainMessage = current($messages);

            $this->handle($domainMessage);
            $this->repository->remove($message);
        }

        $this->repository->commit();
    }
}
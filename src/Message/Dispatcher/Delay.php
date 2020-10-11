<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\EventStore\LoadsEvents;
use Phactor\Message\Dispatcher\Delay\DeferredMessage;
use Phactor\Message\Handler;
use Phactor\EventStore\EventStore;
use Phactor\ReadModel\Repository;
use Doctrine\Common\Collections\Criteria;

class Delay implements Handler
{
    private Handler $wrappedHandler;
    private Repository $repository;
    private LoadsEvents $eventStore;

    public function __construct(Handler $wrappedHandler, Repository $repository, LoadsEvents $eventStore)
    {
        $this->wrappedHandler = $wrappedHandler;
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

        $this->wrappedHandler->handle($message);
    }

    public function processMessages()
    {
        $deferredMessages = $this->repository->matching(new Criteria());
        foreach ($deferredMessages as $deferredMessage) {
            /** @var DeferredMessage $deferredMessage */
            if (!$deferredMessage->isDispatchable()) {
                continue;
            }

            $domainMessages = $this->eventStore->loadEventsByIds($deferredMessage->getId());
            if ($domainMessages instanceof \Traversable) {
                $domainMessages = \iterator_to_array($domainMessages);
            }
            //should only be one at this stage as it's not been dispatched
            $domainMessage = current($domainMessages);

            $this->handle($domainMessage);
            $this->repository->remove($deferredMessage);
        }

        $this->repository->commit();
    }
}

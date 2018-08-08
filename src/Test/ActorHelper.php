<?php

namespace Carnage\Phactor\Test;

use Carnage\Phactor\Identity\YouTubeStyleIdentityGenerator;
use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;
use Carnage\Phactor\Message\GenericBus;
use Carnage\Phactor\Message\GenericHandler;
use Carnage\Phactor\Message\MessageFirer;
use Carnage\Phactor\Persistence\ActorRepository;
use Carnage\Phactor\Persistence\InMemoryEventStore;
use Carnage\Phactor\Zend\MessageHandlerManager;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;

class ActorHelper
{
    private $messageBus;
    private $handler;
    private $generator;
    private $eventStore;
    private $repository;
    private $actorClass;

    private $actorIdentity;
    private $triggeredMessages;

    public function __construct($for)
    {
        $this->actorClass = $for;
        $this->generator = new class() extends YouTubeStyleIdentityGenerator {
            private $peaked;

            public function peak()
            {
                $this->peaked = parent::generateIdentity();
                return $this->peaked;
            }

            public function generateIdentity()
            {
                if ($this->peaked !== null) {
                    $peaked = $this->peaked;
                    $this->peaked = null;
                    return $peaked;
                }

                return parent::generateIdentity();
            }
        };

        $this->actorIdentity = new ActorIdentity($for, $this->generator->peak());

        $this->messageBus = new GenericBus(new Logger(new Noop()), [], new MessageHandlerManager(), $this->generator);
        $this->messageFirer = new MessageFirer($this->generator, $this->messageBus);

        $this->eventStore = new InMemoryEventStore();
        $this->repository = new ActorRepository($this->messageBus, $this->eventStore, $this->generator);
        $this->handler = new GenericHandler($for, $this->repository);
    }

    public function given(array $messages)
    {
        $this->generator->generateIdentity();
        foreach ($messages as $message) {
            $domainMessage = $this->prepareMessage($message);
            $this->eventStore->save($this->actorIdentity, $domainMessage);
        }

        return $this;
    }

    public function when($message)
    {
        $this->messageBus->subscribe(\get_class($message), $this->handler);
        $this->triggeredMessages = $this->messageFirer->fire($message);

        return $this;
    }

    public function expect(array $messages)
    {
        $strippedMessages = \array_map(function (DomainMessage $domainMessage) { return $domainMessage->getMessage(); }, $this->triggeredMessages);

        if (empty($messages)) {
            assert(empty($strippedMessages));
            //strippedMessages must be empty
        } else {
            //loop through and test each message is equal.
        }
    }

    public function getActorIdentity()
    {
        return $this->actorIdentity;
    }

    private function prepareMessage($messageOrDomainMessage)
    {
        if ($messageOrDomainMessage instanceof DomainMessage) {
            $message = $messageOrDomainMessage->getMessage();
            $domainMessage = $messageOrDomainMessage;
        } else {
            $message = $messageOrDomainMessage;
            $domainMessage = DomainMessage::anonMessage($this->generator->generateIdentity(), $message);
        }



        return $domainMessage;
    }
}
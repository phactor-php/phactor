<?php

namespace Phactor\Test;

use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\ActorIdentity;
use Phactor\Message\DomainMessage;
use Phactor\Message\GenericBus;
use Phactor\Message\GenericHandler;
use Phactor\Message\MessageFirer;
use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\InMemoryEventStore;
use Phactor\Zend\MessageHandlerManager;
use PHPUnit\Framework\Assert;
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

    public function expectNoMessages()
    {
        $strippedMessages = $this->stripMessages();

        Assert::assertEmpty($strippedMessages);
    }

    public function expect(array $messages)
    {
        $strippedMessages = $this->stripMessages();

        Assert::assertArraySubset($messages, $strippedMessages, false);
    }

    public function expectOnly(array $messages)
    {
        $strippedMessages = $this->stripMessages();

        Assert::assertEquals($messages, $strippedMessages, false);
    }

    public function getActorIdentity()
    {
        return $this->actorIdentity;
    }

    private function prepareMessage($messageOrDomainMessage)
    {
        if ($messageOrDomainMessage instanceof DomainMessage) {
            return $messageOrDomainMessage;
        }

        return  DomainMessage::anonMessage($this->generator->generateIdentity(), $messageOrDomainMessage);
    }

    /**
     * @return array
     */
    private function stripMessages(): array
    {
        return  \array_map(function (DomainMessage $domainMessage) {
            return $domainMessage->getMessage();
        }, $this->triggeredMessages);
    }
}
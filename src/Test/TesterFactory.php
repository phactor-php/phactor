<?php

namespace Phactor\Test;

use Phactor\Actor\ActorSubscriptionPersistor;
use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\ActorIdentity;
use Phactor\Message\GenericBus;
use Phactor\Message\GenericHandler;
use Phactor\Message\MessageFirer;
use Phactor\Message\MessageSubscriptionProvider;
use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\InMemoryEventStore;
use Phactor\ReadModel\InMemoryRepository;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;

class TesterFactory
{
    private $generator;
    private $container;
    private $genericBus;
    private $messageTester;
    private $messageFirer;

    public function __construct()
    {
        $this->generator = new LinearGenerator();
        $this->container = new TestContainer();
        $this->genericBus = new GenericBus((new Logger())->addWriter(new Noop()), [], $this->container, []);
        $this->messageTester = new MessageTester();
        $this->genericBus->stream($this->messageTester);
        $this->messageFirer = new MessageFirer(new YouTubeStyleIdentityGenerator(), $this->genericBus);
    }

    public function actor(string $class, MessageSubscriptionProvider $subscriptionProvider)
    {
        $actorIdentity = new ActorIdentity($class, $this->generator->getNextId());
        $eventStore = new InMemoryEventStore();
        $subscriptionRepository = new InMemoryRepository();
        $repository = new ActorRepository($this->genericBus, $eventStore, $this->generator, new ActorSubscriptionPersistor($subscriptionRepository));
        $handler = new GenericHandler($class, $repository);
        $this->container->setService($class, $handler);

        foreach ($subscriptionProvider->getSubscriptions() as $message => $subscribers) {
            if (in_array($class, $subscribers)) {
                $this->genericBus->lazySubscribe($message, $class);
            }
        }

        return new ActorTester($this->messageTester, $this->messageFirer, $eventStore, $actorIdentity);
    }

    public function handler(callable $factory, MessageSubscriptionProvider $subscriptionProvider)
    {
        $handler = $factory($this->genericBus, $this->generator);
        $class = get_class($handler);

        foreach ($subscriptionProvider->getSubscriptions() as $message => $subscribers) {
            if (in_array($class, $subscribers)) {
                $this->genericBus->subscribe($message, $handler);
            }
        }

        return new HandlerTester($this->messageTester, $this->messageFirer);
    }
}
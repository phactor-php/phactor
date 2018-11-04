<?php

namespace Phactor\Zend;

use Phactor\Identity\Generator;
use Phactor\Message\DelayedMessage\DeferredMessage;
use Phactor\Message\DelayedMessage\DelayedMessageBus;
use Phactor\Message\GenericBus;
use Phactor\Persistence\EventStore;
use Interop\Container\ContainerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\ServiceManager\Factory\FactoryInterface;

class BusFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $subscriptions = $container->get('Config')['message_subscriptions'];
        $genericBus = new GenericBus(
        //$container->get('Log'),
            (new Logger())->addWriter(new Noop()),
            $subscriptions,
            $container->get(MessageHandlerManager::class),
            $container->get(Generator::class)
        );

        $repository = $container->get(RepositoryManager::class)->get(DeferredMessage::class);

        return new DelayedMessageBus($genericBus, $repository, $container->get(EventStore::class));
    }
}

<?php

namespace Phactor\Zend;

use Interop\Container\ContainerInterface;
use Phactor\Actor\ActorSubscriptionPersistor;
use Phactor\Actor\Subscription;
use Phactor\Identity\Generator;
use Phactor\Message\Bus;
use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\EventStore;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActorRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $subscriptionRepository = $container->get(RepositoryManager::class)->get(Subscription::class);
        return new ActorRepository(
            $container->get(Bus::class),
            $container->get(EventStore::class),
            $container->get(Generator::class),
            new ActorSubscriptionPersistor($subscriptionRepository)
        );
    }
}

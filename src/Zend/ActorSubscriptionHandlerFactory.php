<?php


namespace Phactor\Zend;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Phactor\Actor\ActorSubscriptionHandler;
use Phactor\Actor\Subscription;
use Phactor\Persistence\ActorRepository;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ActorSubscriptionHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $subscriptionRepository = $container->get(RepositoryManager::class)->get(Subscription::class);
        $actorRepository = $container->get(ActorRepository::class);
        return new ActorSubscriptionHandler($subscriptionRepository, $actorRepository);
    }

}
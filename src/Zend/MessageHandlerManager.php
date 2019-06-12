<?php

namespace Phactor\Zend;

use Phactor\Actor\ActorInterface;
use Phactor\Actor\ActorSubscriptionPersistor;
use Phactor\Actor\Subscription;
use Phactor\Identity\Generator;
use Phactor\Message\Bus;
use Phactor\Message\GenericHandler;
use Phactor\Message\Handler;
use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\EventStore;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class MessageHandlerManager extends AbstractPluginManager
{
    /**
     * Whether or not to auto-add a FQCN as an invokable if it exists.
     *
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * An object type that the created instance must be instanced of
     *
     * @var null|string
     */
    protected $instanceOf = Handler::class;

    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        $config['abstract_factories'][] = new class() implements AbstractFactoryInterface
        {
            public function canCreate(ContainerInterface $container, $requestedName)
            {
                return in_array(ActorInterface::class, \class_implements($requestedName));
            }

            public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
            {
                $subscriptionRepository = $container->get(RepositoryManager::class)->get(Subscription::class);
                $respository = new ActorRepository(
                    $container->get(Bus::class),
                    $container->get(EventStore::class),
                    $container->get(Generator::class),
                    new ActorSubscriptionPersistor($subscriptionRepository)
                );
                return new GenericHandler($requestedName, $respository);
            }
        };
        parent::__construct($configInstanceOrParentLocator, $config);
    }
}
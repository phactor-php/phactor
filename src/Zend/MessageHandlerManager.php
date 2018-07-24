<?php

namespace Carnage\Phactor\Zend;

use Carnage\Phactor\Actor\ActorInterface;
use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\Bus;
use Carnage\Phactor\Message\GenericHandler;
use Carnage\Phactor\Message\Handler;
use Carnage\Phactor\Persistence\ActorRepository;
use Carnage\Phactor\Persistence\EventStore;
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
                return $requestedName instanceof ActorInterface;
            }

            public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
            {
                $respository = new ActorRepository($container->get(Bus::class), $container->get(EventStore::class), $container->get(Generator::class));
                return new GenericHandler($requestedName, $respository);
            }
        };
        parent::__construct($configInstanceOrParentLocator, $config);
    }
}
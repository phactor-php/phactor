<?php

namespace Carnage\Phactor\Zend;

use Carnage\Phactor\Actor\ActorInterface;
use Carnage\Phactor\Doctrine\OrmRepository;
use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\Bus;
use Carnage\Phactor\Message\GenericHandler;
use Carnage\Phactor\Message\Handler;
use Carnage\Phactor\Persistence\ActorRepository;
use Carnage\Phactor\Persistence\EventStore;
use Carnage\Phactor\ReadModel\InMemoryRepository;
use Carnage\Phactor\ReadModel\Repository;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class InMemoryRepositoryManager extends AbstractPluginManager implements RepositoryManager
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
    protected $instanceOf = Repository::class;

    public function __construct($configInstanceOrParentLocator = null, array $config = [])
    {
        $config['abstract_factories'][] = new class() implements AbstractFactoryInterface
        {
            public function canCreate(ContainerInterface $container, $requestedName)
            {
                //Perhaps add a check that the requested name is a valid entity
                return true;
            }

            public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
            {
                return new InMemoryRepository();
            }
        };
        parent::__construct($configInstanceOrParentLocator, $config);
    }
}
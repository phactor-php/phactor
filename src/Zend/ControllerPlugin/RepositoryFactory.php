<?php

namespace Carnage\Phactor\Zend\ControllerPlugin;

use Carnage\Phactor\Zend\RepositoryManager;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Repository($container->get(RepositoryManager::class));
    }
}
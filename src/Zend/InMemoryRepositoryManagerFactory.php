<?php


namespace Carnage\Phactor\Zend;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class InMemoryRepositoryManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new InMemoryRepositoryManager($container, []);
    }
}

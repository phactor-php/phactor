<?php


namespace Phactor\Doctrine\Zend;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class OrmRepositoryManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new OrmRepositoryManager($container, []);
    }
}
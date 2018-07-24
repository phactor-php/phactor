<?php


namespace Carnage\Phactor\Zend\ControllerPlugin;


use Carnage\Phactor\Message\Bus;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MessageBusFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new MessageBus($container->get(Bus::class));
    }
}
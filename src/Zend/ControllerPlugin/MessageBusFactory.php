<?php


namespace Carnage\Phactor\Zend\ControllerPlugin;


use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\Bus;
use Carnage\Phactor\Message\MessageFirer;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class MessageBusFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $messageBus = $container->get(Bus::class);
        $identityGenerator = $container->get(Generator::class);

        return new MessageBus(new MessageFirer($identityGenerator, $messageBus));
    }
}
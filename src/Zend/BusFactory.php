<?php

namespace Carnage\Phactor\Zend;

use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\GenericBus;
use Interop\Container\ContainerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\ServiceManager\Factory\FactoryInterface;

class BusFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $subscriptions = $container->get('Config')['message_subscriptions'];
        return new GenericBus(
        //$container->get('Log'),
            (new Logger())->addWriter(new Noop()),
            $subscriptions,
            $container->get(MessageHandlerManager::class),
            $container->get(Generator::class)
        );
    }
}

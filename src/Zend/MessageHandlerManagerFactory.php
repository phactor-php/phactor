<?php


namespace Phactor\Zend;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\ArrayUtils;

class MessageHandlerManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $config = ArrayUtils::merge($config['message_handlers'], $config['phactor']['message_handlers']);
        return new MessageHandlerManager($container, $config);
    }
}
<?php

namespace Phactor\Zend;

use Phactor\Auth\AnonUser;
use Phactor\Auth\AuthorisationDelegator;
use Phactor\Identity\Generator;
use Phactor\Message\Bus;
use Phactor\Message\GenericBus;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

/** @TODO change to delegator factory */
class AuthBusFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $rbac = $config['phactor']['message_rbac'];

        $wrappedBus = $container->get(Bus::class);

        $auth = $container->get(AuthenticationServiceInterface::class);

        $user = new AnonUser();

        if ($auth->hasIdentity()) {
            //@TODO handle instance where this identity doesn't implement our interface (provide config to wrap it)
            $user = $auth->getIdentity();
        }

        return new AuthorisationDelegator($wrappedBus, $rbac, $user);
    }
}
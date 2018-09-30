<?php


namespace Carnage\Phactor\Zend;


use Carnage\Phactor\Auth\AuthorisationDelegator;
use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Identity\YouTubeStyleIdentityGenerator;
use Carnage\Phactor\Message\Bus;
use Carnage\Phactor\Persistence\EventStore;
use Carnage\Phactor\Persistence\InMemoryEventStore;
use Carnage\Phactor\Zend\ControllerPlugin\MessageBusFactory;
use Carnage\Phactor\Zend\ControllerPlugin\RepositoryFactory;

class Module
{
    public function getConfig()
    {
        return [
            'service_manager' => [
                'aliases' => [
                    Generator::class => YouTubeStyleIdentityGenerator::class,
                    EventStore::class => InMemoryEventStore::class,
                    RepositoryManager::class => InMemoryRepositoryManager::class,
                ],
                'invokables' => [
                    YouTubeStyleIdentityGenerator::class => YouTubeStyleIdentityGenerator::class,
                    InMemoryEventStore::class => InMemoryEventStore::class
                ],
                'factories' => [
                    MessageHandlerManager::class => MessageHandlerManagerFactory::class,
                    Bus::class => BusFactory::class,
                    InMemoryRepositoryManager::class => InMemoryRepositoryManagerFactory::class,
                    AuthorisationDelegator::class => AuthBusFactory::class,
                ]
            ],
            'controller_plugins' => [
                'factories' => [
                    'messageBus' => MessageBusFactory::class,
                    'repository' => RepositoryFactory::class,
                ]
            ],
            'message_handlers' => [],
            'message_subscriptions' => [],
        ];
    }
}
<?php


namespace Phactor\Zend;


use Phactor\Auth\AuthorisationDelegator;
use Phactor\Identity\Generator;
use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\Bus;
use Phactor\Persistence\EventStore;
use Phactor\Persistence\InMemoryEventStore;
use Phactor\Zend\Cli\Cron;
use Phactor\Zend\Cli\CronFactory;
use Phactor\Zend\ControllerPlugin\MessageBusFactory;
use Phactor\Zend\ControllerPlugin\RepositoryFactory;

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
            'cli_commands' => [
                'factories' => [
                    Cron::class => CronFactory::class,
                ]
            ],
            'message_handlers' => [],
            'message_subscriptions' => [],
        ];
    }
}
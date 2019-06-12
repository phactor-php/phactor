<?php


namespace Phactor\Zend;


use Phactor\Actor\ActorSubscriptionHandler;
use Phactor\Auth\AuthorisationDelegator;
use Phactor\Identity\Generator;
use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\Bus;
use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\EventStore;
use Phactor\Persistence\InMemoryEventStore;
use Phactor\Zend\Cli\Cron;
use Phactor\Zend\Cli\CronFactory;
use Phactor\Zend\ControllerPlugin\MessageBusFactory;
use Phactor\Zend\ControllerPlugin\RepositoryFactory;
use Zend\ServiceManager\Proxy\LazyServiceFactory;

class Module
{
    public function getConfig()
    {
        return [
            'service_manager' => [
                'aliases' => [
                    EventStore::class => InMemoryEventStore::class,
                    Generator::class => YouTubeStyleIdentityGenerator::class,
                    RepositoryManager::class => InMemoryRepositoryManager::class,
                ],
                'delegators' => [
                    ActorRepository::class => [
                        LazyServiceFactory::class,
                    ],
                ],
                'invokables' => [
                    InMemoryEventStore::class => InMemoryEventStore::class,
                    YouTubeStyleIdentityGenerator::class => YouTubeStyleIdentityGenerator::class,
                ],
                'factories' => [
                    ActorRepository::class => ActorRepositoryFactory::class,
                    AuthorisationDelegator::class => AuthBusFactory::class,
                    Bus::class => BusFactory::class,
                    InMemoryRepositoryManager::class => InMemoryRepositoryManagerFactory::class,
                    MessageHandlerManager::class => MessageHandlerManagerFactory::class,
                ]
            ],
            'lazy_services' => array(
                'class_map' => array(
                    ActorRepository::class => ActorRepository::class,
                ),
            ),
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
            'message_subscription_providers' => [],

            'phactor' => [
                'bus_logger' => null,
                'message_handlers' => [
                    'factories' => [
                        ActorSubscriptionHandler::class => ActorSubscriptionHandlerFactory::class,
                    ],
                ],
                'message_rbac' => [],
                'message_stream_subscribers' => [
                    ActorSubscriptionHandler::class,
                ],
                'message_subscriptions' => [],
                'message_subscription_providers' => [],
            ],
        ];
    }
}
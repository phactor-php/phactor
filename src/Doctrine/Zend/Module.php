<?php


namespace Phactor\Doctrine\Zend;


use Phactor\Doctrine\Dbal\JsonObject;
use Phactor\Doctrine\OrmEventStore;
use Phactor\Persistence\EventStore;
use Phactor\Zend\RepositoryManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;

class Module
{
    public function getConfig()
    {
        return [
            'service_manager' => [
                'aliases' => [
                    EventStore::class => OrmEventStore::class,
                    RepositoryManager::class => OrmRepositoryManager::class,
                ],
                'factories' => [
                    OrmEventStore::class => OrmEventStoreFactory::class,
                    OrmRepositoryManager::class => OrmRepositoryManagerFactory::class,
                ]
            ],
            'message_handlers' => [],
            'message_subscriptions' => [],
            'doctrine' => [
                'configuration' => [
                    'orm_default' => [
                        'types' => [
                            'json_object' => JsonObject::class
                        ]
                    ]
                ],
                'connection' => [
                    'orm_default' => [
                        'doctrine_type_mappings' => [
                            'json_object' => 'json_object'
                        ],
                    ]
                ],
                'driver' => [
                    'phactor' => [
                        'class' => XmlDriver::class,
                        'cache' => 'array',
                        'paths' => [__DIR__ . '/../mapping']
                    ],
                    'orm_default' => [
                        'drivers' => [
                            'Phactor\Message' => 'phactor',
                            'Phactor\Actor' => 'phactor',
                        ]
                    ]
                ],
            ],
        ];
    }
}
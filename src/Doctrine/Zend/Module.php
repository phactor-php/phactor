<?php


namespace Carnage\Phactor\Doctrine\Zend;


use Carnage\Phactor\Doctrine\OrmEventStore;
use Carnage\Phactor\Persistence\EventStore;
use Carnage\Phactor\Zend\RepositoryManager;

class Module
{
    public function getConfig()
    {
        return [
            'services' => [
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
        ];
    }
}
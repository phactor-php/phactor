<?php

namespace Phactor\Zend;

use Phactor\Message\DelayedMessage\DeferredMessage;
use Phactor\Message\DelayedMessage\DelayedMessageBus;
use Phactor\Message\GenericBus;
use Phactor\Message\MessageSubscriptionProvider;
use Phactor\Persistence\EventStore;
use Interop\Container\ContainerInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\ArrayUtils;

class BusFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $phactorConfig = $container->get('Config')['phactor'];

        $subscriptions = ArrayUtils::merge($container->get('Config')['message_subscriptions'], $phactorConfig['message_subscriptions']);
        $providers = ArrayUtils::merge($container->get('Config')['message_subscription_providers'], $phactorConfig['message_subscription_providers']);

        foreach ($providers as $provider) {
            $providerInstance = new $provider();
            
            if ($providerInstance instanceof MessageSubscriptionProvider) {
                $newSubscriptions = $providerInstance->getSubscriptions();
            } else {
                $newSubscriptions = $provider::getSubscriptions();
            }

            $subscriptions = ArrayUtils::merge($subscriptions, $newSubscriptions);
        }

        if ($phactorConfig['bus_logger'] === null) {
            $logger = (new Logger())->addWriter(new Noop());
        } else {
            $logger = $container->get($phactorConfig['bus_logger']);
        }

        $messageHandlerManager = $container->get(MessageHandlerManager::class);
        $genericBus = new GenericBus(
            $logger,
            $subscriptions,
            $messageHandlerManager,
            $phactorConfig['message_stream_subscribers']
        );

        $delayedMessageRepository = $container->get(RepositoryManager::class)->get(DeferredMessage::class);

        return new DelayedMessageBus($genericBus, $delayedMessageRepository, $container->get(EventStore::class));
    }
}

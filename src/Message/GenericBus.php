<?php

namespace Phactor\Message;

use Psr\Container\ContainerInterface;
use Zend\Log\LoggerInterface;

final class GenericBus implements Bus
{
    private $log;
    private $subscriptions;
    private $globalSubscriptions = [];
    private $container;

    private $isDispatching = false;
    private $queue = [];

    public function __construct(LoggerInterface $log, array $subscriptions, ContainerInterface $container, array $streamSubscriptions = [])
    {
        $this->log = $log;
        $this->subscriptions = $subscriptions;
        $this->container = $container;
        $this->globalSubscriptions = $streamSubscriptions;
    }

    public function lazySubscribe(string $identifier, string $handler): void
    {
        if (!($this->container->has($handler))) {
            throw new \Exception(sprintf('Unknown handler %s', $handler));
        }

        $this->subscriptions[$identifier][] = $handler;
    }

    public function lazyStream(string $handler): void
    {
        if (!($this->container->has($handler))) {
            throw new \Exception(sprintf('Unknown handler %s', $handler));
        }

        $this->globalSubscriptions[] = $handler;
    }

    public function stream(Handler $handler): void
    {
        $this->globalSubscriptions[] = $handler;
    }

    public function subscribe(string $identifier, Handler $handler): void
    {
        $this->subscriptions[$identifier][] = $handler;
    }

    public function handle(DomainMessage $message): void
    {
        $this->queue[] = $message;
        if ($this->isDispatching) {
            return;
        }

        do {
            $next = array_shift($this->queue);
            $this->dispatch($next);
        } while (!empty($this->queue));
    }

    private function dispatch(DomainMessage $domainMessage): void
    {
        $this->isDispatching = true;

        foreach ($this->globalSubscriptions as $handler) {
            $subscriber = $this->getSubscriber($handler);
            $this->log->info(sprintf('Global handler %s invoked', get_class($subscriber)));
            $subscriber->handle($domainMessage);
        }

        $messageClass = get_class($domainMessage->getMessage());

        $interfaces = class_implements($messageClass);
        array_unshift($interfaces, $messageClass);

        foreach ($interfaces as $interface) {
            $this->log->info(sprintf('Dispatching %s (from %s)', $interface, $messageClass));
            $this->handleSubscribers($domainMessage, $interface);
        }

        $correlationId = $domainMessage->getCorrelationId();
        $this->log->info(sprintf('Dispatching correlation Id %s (from %s)', $correlationId, $messageClass));
        $this->handleSubscribers($domainMessage, $correlationId);

        $this->isDispatching = false;
    }

    private function getSubscriber($subscriber)
    {
        if ($subscriber instanceof Handler) {
            return $subscriber;
        }

        return $this->container->get($subscriber);
    }

    private function handleSubscribers(DomainMessage $domainMessage, string $correlationId): void
    {
        if (isset($this->subscriptions[$correlationId])) {
            $this->log->info(
                sprintf(
                    'Found %d handlers',
                    count($this->subscriptions[$correlationId])
                )
            );

            foreach ((array) $this->subscriptions[$correlationId] as $handler) {
                $subscriber = $this->getSubscriber($handler);
                $this->log->info(sprintf('Handler %s invoked', get_class($subscriber)));
                $subscriber->handle($domainMessage);
            }
        } else {
            $this->log->info('No handlers found');
        }
    }
}
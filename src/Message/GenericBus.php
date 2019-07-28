<?php

namespace Phactor\Message;

use Phactor\Identity\Generator;
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

    public function __construct(LoggerInterface $log, $subscriptions, ContainerInterface $container)
    {
        $this->log = $log;
        $this->subscriptions = $subscriptions;
        $this->container = $container;
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

    /**
     * @param DomainMessage $domainMessage
     */
    private function dispatch(DomainMessage $domainMessage): void
    {
        $this->isDispatching = true;
        $messageClass = get_class($domainMessage->getMessage());

        $interfaces = class_implements($messageClass);
        array_unshift($interfaces, $messageClass);

        foreach ($interfaces as $interface) {
            $this->log->info(sprintf('Dispatching %s (from %s)', $interface, $messageClass));
            if (isset($this->subscriptions[$interface])) {
                $this->log->info(
                    sprintf(
                        'Found %d handlers for: %s (from %s)',
                        count($this->subscriptions[$interface]),
                        $interface,
                        $messageClass
                    )
                );

                foreach ((array)$this->subscriptions[$interface] as $handler) {
                    $this->getSubscriber($handler)->handle($domainMessage);
                }
            }
        }

        $correlationId = $domainMessage->getCorrelationId();
        if (isset($this->subscriptions[$correlationId])) {
            foreach ((array) $this->subscriptions[$correlationId] as $handler) {
                $this->getSubscriber($handler)->handle($domainMessage);
            }
        }

        foreach ($this->globalSubscriptions as $handler) {
            $this->getSubscriber($handler)->handle($domainMessage);
        }

        $this->isDispatching = false;
    }

    private function getSubscriber($subscriber)
    {
        if ($subscriber instanceof Handler) {
            return $subscriber;
        }

        return $this->container->get($subscriber);
    }
}
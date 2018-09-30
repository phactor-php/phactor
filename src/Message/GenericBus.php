<?php

namespace Carnage\Phactor\Message;

use Carnage\Phactor\Identity\Generator;
use Psr\Container\ContainerInterface;
use Zend\Log\LoggerInterface;

final class GenericBus implements Bus
{
    private $log;
    private $subscriptions;
    private $container;
    private $identityGenerator;

    private $isDispatching = false;
    private $queue = [];

    /**
     * Bus constructor.
     * @param $log
     * @param $subscriptions
     * @param $container
     * @param $identityGenerator
     */
    public function __construct(LoggerInterface $log, $subscriptions, ContainerInterface $container, Generator $identityGenerator)
    {
        $this->log = $log;
        $this->subscriptions = $subscriptions;
        $this->container = $container;
        $this->identityGenerator = $identityGenerator;
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
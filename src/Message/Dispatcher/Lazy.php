<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Handler;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Lazy implements Handler
{
    private array $subscriptions;
    private LoggerInterface $log;
    private ContainerInterface $container;

    public function __construct(array $subscriptions, ContainerInterface $container, ?LoggerInterface $log = null)
    {
        if ($log === null) {
            $log = new NullLogger();
        }

        $this->log = $log;
        $this->container = $container;

        foreach ($subscriptions as $event => $handlers) {
            foreach ((array) $handlers as $handler) {
                $this->subscribe($event, $handler);
            }
        }
    }

    public function subscribe(string $event, string $service): void
    {
        if (!($this->container->has($service))) {
            throw new \Exception(sprintf('Unknown handler %s', $service));
        }

        $this->subscriptions[$event][] = $service;
    }

    public function handle(DomainMessage $domainMessage): void
    {
        $messageClass = get_class($domainMessage->getMessage());

        $interfaces = class_implements($messageClass);
        array_unshift($interfaces, $messageClass);

        foreach ($interfaces as $interface) {
            $this->log->info(sprintf('Dispatching %s (from %s)', $interface, $messageClass));
            if (isset($this->subscriptions[$interface])) {
                $this->log->info(
                    sprintf(
                        'Found %d handlers',
                        count($this->subscriptions[$interface])
                    )
                );

                foreach ((array)$this->subscriptions[$interface] as $handlerName) {
                    $handler = $this->container->get($handlerName);
                    $this->log->info(sprintf('Handler %s invoked', get_class($handler)));
                    $handler->handle($domainMessage);
                }
            } else {
                $this->log->info('No handlers found');
            }
        }
    }
}

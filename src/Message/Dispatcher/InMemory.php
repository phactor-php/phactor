<?php

namespace Phactor\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Handler;
use Psr\Log\LoggerInterface;

class InMemory implements Handler
{
    private $subscriptions;
    private $log;

    public function __construct(array $subscriptions, LoggerInterface $log)
    {
        foreach ($subscriptions as $event => $handlers) {
            foreach ((array) $handlers as $handler) {
                $this->subscribe($event, $handler);
            }
        }

        $this->log = $log;
    }

    public function subscribe(string $event, Handler $handler): void
    {
        $this->subscriptions[$event][] = $handler;
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

                foreach ((array)$this->subscriptions[$interface] as $handler) {
                    $this->log->info(sprintf('Handler %s invoked', get_class($handler)));
                    $handler->handle($domainMessage);
                }
            } else {
                $this->log->info('No handlers found');
            }
        }
    }
}

<?php

namespace Carnage\Phactor\Message;

use Carnage\Phactor\Identity\GeneratorInterface;

class GenericBus implements Bus
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
    public function __construct($log, $subscriptions, $container, GeneratorInterface $identityGenerator)
    {
        $this->log = $log;
        $this->subscriptions = $subscriptions;
        $this->container = $container;
        $this->identityGenerator = $identityGenerator;
    }


    public function fire(object $message)
    {
        $domainMessage = DomainMessage::anonMessage($this->identityGenerator->generateIdentity(), $message);
        $this->handle($domainMessage);
    }

    public function handle(DomainMessage $message)
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
     * @param DomainMessage $message
     */
    private function dispatch(DomainMessage $message): void
    {
        $this->isDispatching = true;
        $messageClass = get_class($message);

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
                    $this->container->get($handler)->handle($message);
                }
            }
        }

        $this->isDispatching = false;
    }
}
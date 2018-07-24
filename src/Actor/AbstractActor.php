<?php

namespace Carnage\Phactor\Actor;

use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;

class AbstractActor implements ActorInterface
{
    private const APPLY_PREFIX = 'apply';
    private const HANDLE_PREFIX = 'handle';

    private $identityGenerator;

    private $correlationId;
    private $causationId;

    private $version = 0;
    private $history = [];

    private $queue = [];
    private $id;

    public function __construct(Generator $identityGenerator, string $id = null)
    {
        $this->identityGenerator = $identityGenerator;
        if ($id === null) {
            $id = $this->identityGenerator->generateIdentity();
        }

        $this->id = $id;
    }

    public function handle(DomainMessage $message)
    {
        $this->correlationId = $message->getCorrelationId();
        $this->causationId = $message->getId();
        $this->version++;

        $message = $message->forActor(ActorIdentity::fromActor($this), $this->version);

        $this->history[$this->version] = $message;
        $this->queue[$this->version] = $message;

        $this->call($message, static::HANDLE_PREFIX);

        foreach ($this->queue as $queuedMessage) {
            $this->call($queuedMessage, static::APPLY_PREFIX);
        }
    }

    public static function fromHistory(Generator $identityGenerator, string $id, DomainMessage ...$history)
    {
        $instance = new static($identityGenerator, $id);
        $instance->history = $history;

        foreach ($history as $message) {
            $instance->call($message, static::APPLY_PREFIX);
        }

        return $instance;
    }

    public function newMessages(): array
    {
        return $this->queue;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function committed(): void
    {
        $this->queue = [];
    }

    protected function fire($message)
    {
        $this->version++;
        $this->queue[$this->version] = DomainMessage::recordMessage(
            $this->identityGenerator->generateIdentity(),
            $this->correlationId,
            $this->causationId,
            new ActorIdentity(get_class($this), $this->id),
            $this->version,
            $message
        );
    }

    private function getMethodFor($message, string $prefix): string
    {
        $classParts = explode('\\', get_class($message));
        return $prefix . end($classParts);
    }

    private function call(DomainMessage $message, string $prefix): void
    {
        $method = $this->getMethodFor($message->getMessage(), $prefix);

        if (method_exists($this, $method)) {
            $this->$method($message->getMessage());
        }
    }
}
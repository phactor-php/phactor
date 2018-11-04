<?php

namespace Phactor\Actor;

use Phactor\Identity\Generator;
use Phactor\Message\ActorIdentity;
use Phactor\Message\DomainMessage;

class AbstractActor implements ActorInterface
{
    private const APPLY_PREFIX = 'apply';
    private const HANDLE_PREFIX = 'handle';

    private $identityGenerator;

    private $version = 0;

    /** @var DomainMessage */
    private $handlingMessage;

    private $history = [];
    private $producedMessages = [];
    private $handledMessages = [];
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
        $this->handlingMessage = $message;
        $this->version++;

        $message = $message->forActor(ActorIdentity::fromActor($this), $this->version);

        $this->history[$this->version] = $message;
        $this->handledMessages[$this->version] = $message;

        $this->call($message, self::HANDLE_PREFIX);
        $this->call($message, self::APPLY_PREFIX);

        foreach ($this->producedMessages as $queuedMessage) {
            $this->call($queuedMessage, self::APPLY_PREFIX);
        }

        $this->handlingMessage = null;
    }

    public static function fromHistory(Generator $identityGenerator, string $id, DomainMessage ...$history)
    {
        $instance = new static($identityGenerator, $id);
        $instance->history = $history;

        foreach ($history as $message) {
            $instance->version++;
            $instance->call($message, self::APPLY_PREFIX);
        }

        return $instance;
    }

    public function newHistory(): array
    {
        return array_merge($this->handledMessages, $this->producedMessages);
    }

    public function publishableMessages(): array
    {
        return $this->producedMessages;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function committed(): void
    {
        $this->producedMessages = [];
        $this->handledMessages = [];
    }

    protected function fire($message)
    {
        $this->version++;
        $domainMessage = DomainMessage::recordMessage(
            $this->identityGenerator->generateIdentity(),
            $this->handlingMessage,
            new ActorIdentity(get_class($this), $this->id),
            $this->version,
            $message
        );
        $domainMessage->withMetadata($this->handlingMessage->getMetadata());
        $this->producedMessages[$this->version] = $domainMessage;
    }

    protected function schedule($message, \DateTime $when)
    {
        $this->version++;
        $domainMessage = DomainMessage::recordFutureMessage(
            $this->identityGenerator->generateIdentity(),
            $when,
            $this->handlingMessage,
            new ActorIdentity(get_class($this), $this->id),
            $this->version,
            $message
        );
        $domainMessage->withMetadata($this->handlingMessage->getMetadata());
        $this->producedMessages[$this->version] = $domainMessage;
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
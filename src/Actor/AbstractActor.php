<?php

namespace Phactor\Actor;

use Phactor\Actor\Subscription\Subscriber;
use Phactor\Identity\Generator;
use Phactor\Actor\ActorIdentity;
use Phactor\DomainMessage;
use Phactor\Actor\HasActorId;

class AbstractActor implements ActorInterface
{
    private const APPLY_PREFIX = 'apply';
    private const HANDLE_PREFIX = 'handle';
    protected const SNAPSHOT_FREQUENCY = 10;

    private Generator $identityGenerator;
    private Subscriber $subscriber;
    private int $version = 0;
    private ?DomainMessage $handlingMessage;
    private array $producedMessages = [];
    private array $handledMessages = [];
    private string $id;

    public function __construct(Generator $identityGenerator, Subscriber $subscriber, string $id = null)
    {
        $this->identityGenerator = $identityGenerator;
        if ($id === null) {
            $id = $this->identityGenerator->generateIdentity();
        }

        $this->id = $id;
        $this->subscriber = $subscriber;

        $this->init();
    }

    protected function init()
    {

    }

    public static function fromSnapshot(string $snapshot, Generator $identityGenerator, Subscriber $subscriber, DomainMessage ...$history): ActorInterface
    {
        $instance = unserialize($snapshot);
        if (!($instance instanceof AbstractActor)) {
            throw new \RuntimeException('Invalid snapshot');
        }

        $instance->identityGenerator = $identityGenerator;
        $instance->subscriber = $subscriber;

        foreach ($history as $message) {
            $instance->version++;
            $instance->call($message, self::APPLY_PREFIX);
        }

        return $instance;
    }

    public static function fromHistory(Generator $identityGenerator, Subscriber $subscriber, string $id, DomainMessage ...$history): ActorInterface
    {
        $instance = new static($identityGenerator, $subscriber, $id);

        foreach ($history as $message) {
            $instance->version++;
            $instance->call($message, self::APPLY_PREFIX);
        }

        return $instance;
    }

    public static function extractId(DomainMessage $message): ?string
    {
        $message = $message->getMessage();
        if ($message instanceof HasActorId) {
            return $message->getActorId();
        }

        return null;
    }

    public static function getSubscriptions(): array
    {
        return [];
    }

    public function handle(DomainMessage $message)
    {
        $this->handlingMessage = $message;
        $this->version++;

        $message = $message->forActor(ActorIdentity::fromActor($this), $this->version);

        $this->handledMessages[$this->version] = $message;

        $this->call($message, self::HANDLE_PREFIX);
        $this->call($message, self::APPLY_PREFIX);

        foreach ($this->producedMessages as $queuedMessage) {
            $this->call($queuedMessage, self::APPLY_PREFIX);
        }

        $this->handlingMessage = null;
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

    public function shouldSnapshot(): bool
    {
        return $this->version >= self::SNAPSHOT_FREQUENCY && $this->version % self::SNAPSHOT_FREQUENCY === 0;
    }

    public function snapshot(): string
    {
        $snapshot = clone $this;
        unset($snapshot->identityGenerator);
        unset($snapshot->subscriber);
        $snapshot->producedMessages = [];
        $snapshot->handledMessages = [];
        $snapshot->handlingMessage = null;

        return serialize($snapshot);
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    protected function fire($message): void
    {
        $this->version++;
        $domainMessage = DomainMessage::recordMessage(
            $this->identityGenerator->generateIdentity(),
            $this->handlingMessage,
            $this->getActorIdentity(),
            $this->version,
            $message
        );
        $domainMessage = $domainMessage->withMetadata($this->handlingMessage->getMetadata());
        $this->producedMessages[$this->version] = $domainMessage;
    }

    protected function schedule($message, \DateTimeImmutable $when): void
    {
        $this->version++;
        $domainMessage = DomainMessage::recordFutureMessage(
            $this->identityGenerator->generateIdentity(),
            $when,
            $this->handlingMessage,
            $this->getActorIdentity(),
            $this->version,
            $message
        );
        $domainMessage = $domainMessage->withMetadata($this->handlingMessage->getMetadata());
        $this->producedMessages[$this->version] = $domainMessage;
    }

    protected function subscribe(string $actor, string $id)
    {
        $this->subscriber->subscribe($this->getActorIdentity(), new ActorIdentity($actor, $id));
    }

    protected function unsubscribe(string $actor, string $id)
    {
        $this->subscriber->unsubscribe($this->getActorIdentity(), new ActorIdentity($actor, $id));
    }

    protected function generateIdentity()
    {
        return $this->identityGenerator->generateIdentity();
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

    private function getActorIdentity(): ActorIdentity
    {
        return new ActorIdentity(get_class($this), $this->id);
    }
}

<?php

namespace Phactor;

use Phactor\Actor\ActorIdentity;

final class DomainMessage
{
    private string $id;
    private string $correlationId;
    private string $causationId;
    private \DateTimeImmutable $time;
    private string $messageClass;
    private object $message;
    private array $metadata = [];
    private ?ActorIdentity $producer = null;
    private \DateTimeImmutable $produced;
    private ?ActorIdentity $actor = null;
    private int $version;
    private \DateTimeImmutable $recorded;

    private function __construct(string $id, object $message)
    {
        $dateTimeImmutable = new \DateTimeImmutable((new \DateTimeImmutable())->format('Y-m-d H:i:s'));
        $this->recorded = $dateTimeImmutable; //always recorded now.
        $this->produced = $dateTimeImmutable;
        $this->time = $dateTimeImmutable;
        $this->id = $id;
        $this->correlationId = $id;
        $this->causationId = $id;
        $this->message = $message;
        $this->messageClass = get_class($message);
    }

    public static function recordMessage(
        string $id,
        ?DomainMessage $from,
        ActorIdentity $actorIdentity,
        int $version,
        object $message
    ): DomainMessage {

        $instance = new static($id, $message);
        $instance->version = $version;
        $instance->actor = $actorIdentity;
        $instance->producer = $actorIdentity;
        $instance->correlationId = $from ? $from->correlationId : $id;
        $instance->causationId = $from ? $from->id : $id;

        return $instance;
    }

    public static function recordFutureMessage(
        string $id,
        \DateTimeImmutable $when,
        ?DomainMessage $from,
        ActorIdentity $actorIdentity,
        int $version,
        object $message
    ): DomainMessage {

        $instance = new static($id, $message);
        $instance->time = new \DateTimeImmutable($when->format('Y-m-d H:i:s'));
        $instance->version = $version;
        $instance->actor = $actorIdentity;
        $instance->producer = $actorIdentity;
        $instance->correlationId = $from ? $from->correlationId : $id;
        $instance->causationId = $from ? $from->id : $id;

        return $instance;
    }

    public static function anonMessage(string $id, object $message): DomainMessage
    {
        return new static($id, $message);
    }

    public function forActor(ActorIdentity $newActor, int $version)
    {
        $instance = clone $this;
        $instance->recorded = new \DateTimeImmutable((new \DateTimeImmutable())->format('Y-m-d H:i:s'));
        $instance->actor = $newActor;
        $instance->version = $version;

        return $instance;
    }

    public function withMetadata($metadata)
    {
        $instance = clone $this;
        $instance->metadata = array_merge($instance->metadata, $metadata);
        return $instance;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCorrelationId(): string
    {
        return $this->correlationId;
    }

    public function getCausationId(): string
    {
        return $this->causationId;
    }

    public function getMessage(): object
    {
        return $this->message;
    }

    public function getActorIdentity(): ?ActorIdentity
    {
        return $this->actor;
    }

    public function isInFuture(): bool
    {
        return new \DateTime() < $this->time;
    }

    public function getTime(): \DateTimeImmutable
    {
        return $this->time;
    }

    public function getProducer(): ?ActorIdentity
    {
        return $this->producer;
    }

    public function isNewMessage(): bool
    {
        return $this->producer === null || $this->actor !== null && $this->actor->equals($this->producer);
    }
}

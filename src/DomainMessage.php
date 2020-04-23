<?php

namespace Phactor;

use Phactor\Actor\ActorIdentity;

final class DomainMessage
{
    private string $id;
    private string $correlationId;
    private string $causationId;
    private \DateTimeImmutable $time;
    private object $message;
    private array $metadata = [];
    private ActorIdentity $producer;
    private \DateTimeImmutable $produced;
    private ActorIdentity $actor;
    private int $version;
    private \DateTimeImmutable $recorded;

    private function __construct(string $id)
    {
        $this->recorded = new \DateTimeImmutable(); //always recorded now.
        $this->produced = new \DateTimeImmutable();
        $this->id = $id;
    }

    public static function recordMessage(
        string $id,
        ?DomainMessage $from,
        ActorIdentity $actorIdentity,
        int $version,
        object $message
    ): DomainMessage {

        $instance = new static($id);
        $instance->time = new \DateTimeImmutable();
        $instance->version = $version;
        $instance->message = $message;
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

        $instance = new static($id);
        $instance->time = $when;
        $instance->version = $version;
        $instance->message = $message;
        $instance->actor = $actorIdentity;
        $instance->producer = $actorIdentity;
        $instance->correlationId = $from ? $from->correlationId : $id;
        $instance->causationId = $from ? $from->id : $id;

        return $instance;
    }

    public static function anonMessage(string $id, object $message): DomainMessage
    {
        $instance = new static($id);
        $instance->time = new \DateTimeImmutable();
        $instance->correlationId = $id;
        $instance->causationId = $id;
        $instance->message = $message;

        return $instance;
    }

    public function forActor(ActorIdentity $newActor, int $version)
    {
        $instance = clone $this;
        $instance->recorded = new \DateTimeImmutable();
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
        return $this->actor !== null && $this->actor->equals($this->producer);
    }
}

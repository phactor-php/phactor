<?php

namespace Phactor\Message;

/**
 * Class DomainMessage
 */
final class DomainMessage
{
    private $id;
    private $correlationId;
    private $causationId;
    private $time;
    private $recorded;
    private $version;
    private $message;
    private $messageClass;
    private $metadata = [];
    private $actorId;
    private $actorClass;
    private $producerClass;
    private $producerId;

    private function __construct(string $id)
    {
        $this->recorded = new \DateTimeImmutable(); //always recorded now.
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
        $instance->time = new \DateTime();
        $instance->version = $version;
        $instance->message = $message;
        $instance->messageClass = get_class($message);
        $instance->actorClass = $actorIdentity->getClass();
        $instance->actorId = $actorIdentity->getId();
        $instance->producerClass = $actorIdentity->getClass();
        $instance->producerId = $actorIdentity->getId();
        $instance->correlationId = $from ? $from->correlationId : $id;
        $instance->causationId = $from ? $from->id : $id;

        return $instance;
    }

    public static function recordFutureMessage(
        string $id,
        \DateTime $when,
        ?DomainMessage $from,
        ActorIdentity $actorIdentity,
        int $version,
        object $message
    ): DomainMessage {

        $instance = new static($id);
        $instance->time = $when;
        $instance->version = $version;
        $instance->message = $message;
        $instance->messageClass = get_class($message);
        $instance->actorClass = $actorIdentity->getClass();
        $instance->actorId = $actorIdentity->getId();
        $instance->producerClass = $actorIdentity->getClass();
        $instance->producerId = $actorIdentity->getId();
        $instance->correlationId = $from ? $from->correlationId : $id;
        $instance->causationId = $from ? $from->id : $id;

        return $instance;
    }

    public static function anonMessage(string $id, object $message): DomainMessage
    {
        $instance = new static($id);
        $instance->time = new \DateTime();
        $instance->correlationId = $id;
        $instance->causationId = $id;
        $instance->message = $message;
        $instance->messageClass = get_class($message);

        return $instance;
    }

    public function forActor(ActorIdentity $newActor, int $version)
    {
        $instance = clone $this;
        $instance->recorded = new \DateTimeImmutable();
        $instance->actorClass = $newActor->getClass();
        $instance->actorId = $newActor->getId();
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
        if ($this->actorId === null) {
            return null;
        }

        return new ActorIdentity($this->actorClass, $this->actorId);
    }

    public function isInFuture()
    {
        return new \DateTime() < $this->time;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getProducer(): ?ActorIdentity
    {
        if ($this->producerId === null) {
            return null;
        }

        return new ActorIdentity($this->producerClass, $this->producerId);
    }
}

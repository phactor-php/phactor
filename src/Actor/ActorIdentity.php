<?php

namespace Phactor\Actor;

final class ActorIdentity
{
    private string $class;
    private string $id;

    public function __construct(string $class, string $id)
    {
        $this->class = $class;
        $this->id = $id;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function equals(ActorIdentity $other): bool
    {
        return $other->class === $this->class && $other->id === $this->id;
    }

    public static function fromActor(ActorInterface $actor)
    {
        return new self(get_class($actor), $actor->id());
    }
}

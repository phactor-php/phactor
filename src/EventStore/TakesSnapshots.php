<?php

namespace Phactor\EventStore;

use Phactor\Actor\ActorIdentity;

interface TakesSnapshots
{
    public function saveSnapshot(ActorIdentity $actorIdentity, int $version, string $snapshot): void;

    public function hasSnapshot(ActorIdentity $actorIdentity): bool;

    public function loadSnapshot(ActorIdentity $actorIdentity): string;

    public function loadFromLastSnapshot(ActorIdentity $actorIdentity): iterable;
}

<?php
namespace Phactor\EventStore;

use Phactor\Actor\ActorIdentity;
use Phactor\DomainMessage;
use Doctrine\Common\Collections\Criteria;

interface EventStore
{
    public function load(ActorIdentity $identity): iterable;

    public function save(ActorIdentity $identity, DomainMessage ...$messages): void;
}

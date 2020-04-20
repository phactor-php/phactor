<?php
namespace Phactor\EventStore;

use Phactor\Actor\ActorIdentity;
use Phactor\DomainMessage;
use Doctrine\Common\Collections\Criteria;

interface EventStore
{
    public function load(ActorIdentity $identity): Iterable;

    public function save(ActorIdentity $identity, DomainMessage ...$messages);

    public function eventsMatching(Criteria $criteria): Iterable;
}

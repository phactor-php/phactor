<?php
namespace Carnage\Phactor\Persistence;

use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;
use Doctrine\Common\Collections\Criteria;

interface EventStore
{
    public function load(ActorIdentity $identity): Iterable;

    public function save(ActorIdentity $identity, DomainMessage ...$messages);

    public function eventsMatching(Criteria $criteria): Iterable;
}
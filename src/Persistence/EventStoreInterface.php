<?php
namespace Carnage\Phactor\Persistence;

use Carnage\Phactor\Message\ActorIdentity;
use Carnage\Phactor\Message\DomainMessage;

interface EventStore
{
    public function load(ActorIdentity $identity);

    public function save(ActorIdentity $identity, DomainMessage ...$messages);
}
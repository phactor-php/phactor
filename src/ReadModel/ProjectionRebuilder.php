<?php

namespace Carnage\Phactor\ReadModel;

use Carnage\Phactor\Message\Handler;
use Carnage\Phactor\Persistence\EventStore;

class ProjectionRebuilder
{
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function rebuild(Handler $projection, $subscriptions = [], $force = false)
    {
        if ($projection instanceof ResettableInterface) {
            $projection->reset();
        } elseif (!$force) {
            throw new \Exception('Cannot reset projection and force wasn\'t specified' );
        }

        if ($projection instanceof PreRebuildInterface) {
            $projection->preRebuild();
        }

        //@TODO check this.
        $events = $this->eventStore->eventsMatching(Criteria::create()->where(Criteria::expr()->in('messageClass', $subscriptions)));

        foreach ($events as $event) {
            $projection->handle($event);
        }

        if ($projection instanceof PostRebuildInterface) {
            $projection->postRebuild();
        }
    }
}
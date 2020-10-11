<?php

namespace Phactor\ReadModel;

use Phactor\EventStore\LoadsEvents;
use Phactor\Message\Handler;

class ProjectionRebuilder
{
    private LoadsEvents $eventStore;

    public function __construct(LoadsEvents $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function rebuild(Handler $projection, array $subscriptions = [], bool $force = false)
    {
        if ($projection instanceof ResettableInterface) {
            $projection->reset();
        } elseif (!$force) {
            throw new \Exception('Cannot reset projection and force wasn\'t specified' );
        }

        if ($projection instanceof PreRebuildInterface) {
            $projection->preRebuild();
        }

        $events = $this->eventStore->loadEventsByClasses(...$subscriptions);

        foreach ($events as $event) {
            $projection->handle($event);
        }

        if ($projection instanceof PostRebuildInterface) {
            $projection->postRebuild();
        }
    }
}

<?php


namespace Phactor\EventStore;


interface LoadsEvents
{
    public function loadEventsByIds(string ...$ids);

    public function loadEventsByClasses(string ...$classes);
}

<?php


namespace Phactor\EventStore;


interface LoadsEvents
{
    public function loadEventsByIds(string ...$ids): iterable;

    public function loadEventsByClasses(string ...$classes): iterable;
}

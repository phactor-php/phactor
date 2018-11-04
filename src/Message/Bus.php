<?php

namespace Phactor\Message;

interface Bus
{
    public function handle(DomainMessage $message): void;

    public function subscribe(string $identifier, Handler $handler): void;
}
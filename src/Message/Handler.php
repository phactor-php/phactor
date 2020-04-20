<?php

namespace Phactor\Message;


use Phactor\DomainMessage;

interface Handler
{
    public function handle(DomainMessage $message): void;
}

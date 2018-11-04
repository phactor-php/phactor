<?php

namespace Phactor\Message;


interface Handler
{
    public function handle(DomainMessage $message);
}
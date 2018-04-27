<?php

namespace Carnage\Phactor\Message;


interface Handler
{
    public function handle(DomainMessage $message);
}
<?php

namespace Carnage\Phactor\Message;

interface Bus
{
    public function fire(object $message);

    public function handle(DomainMessage $message);
}
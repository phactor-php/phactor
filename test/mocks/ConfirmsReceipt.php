<?php

namespace PhactorTestMocks;

use Phactor\DomainMessage;
use Phactor\Message\Handler;

class ConfirmsReceipt implements Handler
{
    public $handled = false;
    public $count = 0;
    public function handle(DomainMessage $message): void
    {
        $this->count++;
        $this->handled = true;
    }
}

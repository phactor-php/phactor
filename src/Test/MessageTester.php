<?php

namespace Phactor\Test;

use Phactor\Message\DomainMessage;
use Phactor\Message\Handler;
use PHPUnit\Framework\Assert;

class MessageTester implements Handler
{
    private $triggeredMessages = [];

    public function handle(DomainMessage $message)
    {
        $this->triggeredMessages[] = $message->getMessage();
    }

    public function expect($message)
    {
        Assert::assertContains(
            $message,
            $this->triggeredMessages,
            '',
            false,
            false
        );

        $idx = \array_search($message, $this->triggeredMessages, false);
        unset($this->triggeredMessages[$idx]);
    }

    public function expectNoMoreMessages()
    {
        Assert::assertEmpty($this->triggeredMessages);
    }
}
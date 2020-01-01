<?php

namespace Phactor\Test;

use Phactor\Message\MessageFirer;

class HandlerTester
{
    private $messageTester;
    private $messageFirer;

    public function __construct(MessageTester $messageTester, MessageFirer $messageFirer)
    {
        $this->messageTester = $messageTester;
        $this->messageFirer = $messageFirer;
    }

    public function given(array $messages)
    {
        foreach ($messages as $message) {
            $this->messageFirer->fire($message);
            $this->expect($message);
        }

        return $this;
    }

    public function when($message)
    {
        $this->messageFirer->fire($message);
        $this->expect($message);

        return $this;
    }

    public function expectNoMoreMessages()
    {
        $this->messageTester->expectNoMoreMessages();
    }

    public function expect($message)
    {
        $this->messageTester->expect($message);
    }
}
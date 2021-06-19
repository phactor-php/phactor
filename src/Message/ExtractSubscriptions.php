<?php

namespace Phactor\Message;

class ExtractSubscriptions implements MessageSubscriptionProvider
{
    private string $from;

    public function __construct(string $from)
    {
        $this->from = $from;
    }

    public function getSubscriptions(): array
    {
        $subscriptions = [];

        foreach ($this->from::getSubscriptions() as $message) {
            $subscriptions[$message][] = $this->from;
        }

        return $subscriptions;
    }
}

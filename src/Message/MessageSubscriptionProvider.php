<?php


namespace Phactor\Message;


interface MessageSubscriptionProvider
{
    public function getSubscriptions(): array;
}
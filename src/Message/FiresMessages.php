<?php


namespace Phactor\Message;


interface FiresMessages
{
    public function fireAndForget(object $message): void;

    public function fire(object $message): array;
}
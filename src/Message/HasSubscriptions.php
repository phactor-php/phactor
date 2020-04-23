<?php


namespace Phactor\Message;


interface HasSubscriptions
{
    public static function getSubscriptions(): array;
}

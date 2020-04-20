<?php


namespace Phactor\Message\Dispatcher\Authorise;


class AnonUser implements User
{
    public function getId(): string
    {
        return '';
    }

    public function getRoles(): Iterable
    {
        return [''];
    }
}

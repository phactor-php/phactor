<?php


namespace Phactor\Message\Dispatcher\Authorise;


interface User
{
    public function getId(): string;

    public function getRoles(): Iterable;
}

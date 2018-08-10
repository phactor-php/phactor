<?php


namespace Carnage\Phactor\Auth;


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
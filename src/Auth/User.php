<?php


namespace Carnage\Phactor\Auth;


interface User
{
    public function getId(): string;

    public function getRoles(): Iterable;
}
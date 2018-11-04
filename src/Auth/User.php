<?php


namespace Phactor\Auth;


interface User
{
    public function getId(): string;

    public function getRoles(): Iterable;
}
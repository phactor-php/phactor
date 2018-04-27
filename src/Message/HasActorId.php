<?php

namespace Carnage\Phactor\Message;


interface HasActorId
{
    public function getActorId(): string;
}
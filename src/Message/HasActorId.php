<?php

namespace Phactor\Message;


interface HasActorId
{
    public function getActorId(): string;
}
<?php

namespace Carnage\Phactor\ReadModel;

interface PostRebuildInterface
{
    public function postRebuild(): void;
}
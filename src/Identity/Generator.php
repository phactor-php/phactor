<?php

namespace Phactor\Identity;

interface Generator
{
    public function generateIdentity(): string;
}

<?php

namespace PhactorTestMocks;

use Phactor\Identity\Generator;

class LinearGenerator implements Generator
{
    private const A0000000000 = 10995116277760;
    private $id = 0;

    public function generateIdentity(): string
    {
        return strtoupper(dechex(self::A0000000000 + ++$this->id));
    }

    public function getNextId(): string
    {
        return strtoupper(dechex(self::A0000000000 + $this->id + 1));
    }
}


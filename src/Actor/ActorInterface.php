<?php

namespace Carnage\Phactor\Actor;

use Carnage\Phactor\Identity\Generator;
use Carnage\Phactor\Message\DomainMessage;

interface ActorInterface
{
    public function __construct(Generator $identityGenerator, string $id = null);

    public static function fromHistory(Generator $identityGenerator, string $id, DomainMessage ...$history);

    public function handle(DomainMessage $message);

    public function newMessages(): array;

    public function id(): string;

    public function committed(): void;
}

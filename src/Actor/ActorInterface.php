<?php

namespace Carnage\Phactor\Actor;

use Carnage\Phactor\Identity\GeneratorInterface;
use Carnage\Phactor\Message\DomainMessage;

interface ActorInterface
{
    public function __construct(GeneratorInterface $identityGenerator, string $id = null);

    public static function fromHistory(GeneratorInterface $identityGenerator, string $id, DomainMessage ...$history);

    public function handle(DomainMessage $message);

    public function newMessages(): array;

    public function id(): string;

    public function committed(): void;
}

<?php

namespace Phactor\Actor;

use Phactor\Identity\Generator;
use Phactor\Message\DomainMessage;

interface ActorInterface
{
    public function __construct(Generator $identityGenerator, Subscriber $subscriber, string $id = null);

    public static function fromHistory(Generator $identityGenerator, Subscriber $subscriber, string $id, DomainMessage ...$history);

    public static function generateId(DomainMessage $message): ?string;

    public function handle(DomainMessage $message);

    public function newHistory(): array;

    public function publishableMessages(): array;

    public function id(): string;

    public function committed(): void;
}

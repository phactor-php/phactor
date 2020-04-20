<?php

namespace Phactor\Actor;

use Phactor\Actor\Subscription\Subscriber;
use Phactor\Identity\Generator;
use Phactor\DomainMessage;

interface ActorInterface
{
    public function __construct(Generator $identityGenerator, Subscriber $subscriber, string $id = null);

    public static function fromHistory(Generator $identityGenerator, Subscriber $subscriber, string $id, DomainMessage ...$history);

    public static function extractId(DomainMessage $message): ?string;

    public function handle(DomainMessage $message);

    public function newHistory(): array;

    public function publishableMessages(): array;

    public function id(): string;

    public function committed(): void;
}

<?php

namespace Phactor\Actor;

use Phactor\Actor\Subscription\Subscriber;
use Phactor\Identity\Generator;
use Phactor\DomainMessage;

interface ActorInterface
{
    public function __construct(Generator $identityGenerator, Subscriber $subscriber, string $id = null);

    public static function fromSnapshot(string $snapshot, Generator $identityGenerator, Subscriber $subscriber, DomainMessage ...$history): ActorInterface;

    public static function fromHistory(Generator $identityGenerator, Subscriber $subscriber, string $id, DomainMessage ...$history): ActorInterface;

    public static function extractId(DomainMessage $message): ?string;

    public static function getSubscriptions(): array;

    public function handle(DomainMessage $message);

    public function newHistory(): array;

    public function publishableMessages(): array;

    public function id(): string;

    public function committed(): void;

    public function shouldSnapshot(): bool;

    public function snapshot(): string;

    public function getVersion(): int;
}

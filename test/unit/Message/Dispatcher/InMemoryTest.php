<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use PhactorTestMocks\ConfirmsReceipt;
use PHPUnit\Framework\TestCase;
use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\InMemory;

/**
 * @covers Phactor\Message\Dispatcher\InMemory
 * @uses Phactor\DomainMessage
 */
class InMemoryTest extends TestCase
{
    public function testSubscribe() : void
    {
        $handler = new ConfirmsReceipt();

        $sut = new InMemory([]);
        $sut->subscribe(\stdClass::class, $handler);
        $sut->handle(DomainMessage::anonMessage('id', new \stdClass()));

        self::assertTrue($handler->handled, 'Handler did not get the message');
    }

    public function testHandle() : void
    {
        $handler = new ConfirmsReceipt();
        $handler2 = new ConfirmsReceipt();

        $sut = new InMemory([\stdClass::class => [$handler], \RuntimeException::class => [$handler2]]);
        $sut->handle(DomainMessage::anonMessage('id', new \stdClass()));

        self::assertTrue($handler->handled, 'Handler did not get the message');
        self::assertFalse($handler2->handled, 'Handler got a message it did not subscribe to');
    }
}

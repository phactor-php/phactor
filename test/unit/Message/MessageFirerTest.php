<?php

declare (strict_types=1);

namespace PhactorTest\Message;

use Phactor\Message\Dispatcher\Capture;
use PhactorTestMocks\ConfirmsReceipt;
use PhactorTestMocks\LinearGenerator;
use PHPUnit\Framework\TestCase;
use Phactor\Message\MessageFirer;

/**
 * @covers Phactor\Message\MessageFirer
 * @uses Phactor\DomainMessage
 * @uses Phactor\Message\Dispatcher\Capture
 */
class MessageFirerTest extends TestCase
{
    public function testFire(): void
    {
        $handler = new ConfirmsReceipt();
        $capture = new Capture($handler);
        $sut = new MessageFirer(new LinearGenerator(), $capture);
        $messages = $sut->fire(new \stdClass());

        self::assertCount(1, $messages);
        self::assertTrue($handler->handled, 'Message not received by handler');
    }

    public function testFireWithIncorrectHandler(): void
    {
        $handler = new ConfirmsReceipt();
        $sut = new MessageFirer(new LinearGenerator(), $handler);

        self::expectException(\RuntimeException::class);

        $sut->fire(new \stdClass());
    }

    public function testFireAndForget(): void
    {
        $handler = new ConfirmsReceipt();
        $sut = new MessageFirer(new LinearGenerator(), $handler);
        $sut->fireAndForget(new \stdClass());

        self::assertTrue($handler->handled, 'Message not received by handler');
    }
}

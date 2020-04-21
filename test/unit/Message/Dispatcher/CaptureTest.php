<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use PhactorTestMocks\ConfirmsReceipt;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\Capture;
use Phactor\Message\Handler;

/**
 * @covers \Phactor\Message\Dispatcher\Capture
 * @uses \Phactor\DomainMessage
 */
class CaptureTest extends TestCase
{
    public function testHandle() : void
    {
        $handler = new ConfirmsReceipt();
        $sut = new Capture($handler);
        $message = DomainMessage::anonMessage('id', new \stdClass());

        $sut->handle($message);

        self::assertTrue($handler->handled, 'Message not passed to wrapped handler');
        self::assertEquals([$message], $sut->capturedMessages());

        $sut->reset();

        self::assertEmpty($sut->capturedMessages());
    }
}

<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use PhactorTestMocks\ConfirmsReceipt;
use PHPUnit\Framework\TestCase;
use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\All;

/**
 * @covers \Phactor\Message\Dispatcher\All
 * @uses \Phactor\DomainMessage
 */
class AllTest extends TestCase
{
    public function testHandle() : void
    {
        $handler1 = new ConfirmsReceipt();
        $handler2 = new ConfirmsReceipt();

        $sut = new All($handler1, $handler2);

        $sut->handle(DomainMessage::anonMessage('id', new \stdClass()));

        self::assertTrue($handler1->handled, 'Handler 1 did not receive the message');
        self::assertTrue($handler2->handled, 'Handler 2 did not receive the message');
    }
}

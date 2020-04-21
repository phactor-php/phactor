<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\Authorise\AccessDenied;
use Phactor\Message\Dispatcher\Authorise\Restricted;
use PhactorTestMocks\ConfirmsReceipt;
use PHPUnit\Framework\TestCase;
use Phactor\Message\Dispatcher\Authorise;
use Phactor\Message\Dispatcher\Authorise\User;

/**
 * @covers \Phactor\Message\Dispatcher\Authorise
 * @uses \Phactor\DomainMessage
 * @uses \Phactor\Message\Dispatcher\Authorise\AccessDenied
 */
class AuthoriseTest extends TestCase
{
    public function testHandle() : void
    {
        $user = new class() implements User {
            public function getId(): string
            {
                return 'userid';
            }

            public function getRoles(): Iterable
            {
                return new \ArrayIterator(['staff']);
            }
        };

        $message = new class () implements Restricted {};

        $handler = new ConfirmsReceipt();
        $sut = new Authorise($handler, [Restricted::class => ['staff']], $user);
        $sut->handle(DomainMessage::anonMessage('id', $message));

        self::assertTrue($handler->handled, 'Handler did not receive message');
    }

    public function testHandleUnauthorised() : void
    {
        $user = new class() implements User {
            public function getId(): string
            {
                return 'userid';
            }

            public function getRoles(): Iterable
            {
                return ['staff'];
            }
        };

        $message = new class () implements Restricted {};

        $handler = new ConfirmsReceipt();
        $sut = new Authorise($handler, [Restricted::class => ['admin']], $user);

        self::expectException(AccessDenied::class);

        $sut->handle(DomainMessage::anonMessage('id', $message));
    }

    public function testHandleUnrestricted() : void
    {
        $user = new class() implements User {
            public function getId(): string
            {
                return 'userid';
            }

            public function getRoles(): Iterable
            {
                return new \ArrayIterator(['staff']);
            }
        };

        $message = new \stdClass();

        $handler = new ConfirmsReceipt();
        $sut = new Authorise($handler, [Restricted::class => ['admin']], $user);
        $sut->handle(DomainMessage::anonMessage('id', $message));

        self::assertTrue($handler->handled, 'Handler did not receive message');
    }
}

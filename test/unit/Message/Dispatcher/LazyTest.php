<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use PhactorTestMocks\ConfirmsReceipt;
use PHPUnit\Framework\TestCase;
use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\Lazy;
use Psr\Container\ContainerInterface;

/**
 * @covers \Phactor\Message\Dispatcher\Lazy
 * @uses \Phactor\DomainMessage
 */
class LazyTest extends TestCase
{
    public function testSubscribe() : void
    {
        $handler = new ConfirmsReceipt();
        $container = new class ($handler) implements ContainerInterface {
            private $handler;

            public function __construct($handler)
            {
                $this->handler = $handler;
            }

            public function get($id)
            {
                return $this->handler;
            }

            public function has($id)
            {
                return $id === ConfirmsReceipt::class;
            }
        };

        $sut = new Lazy([], $container);
        $sut->subscribe(\stdClass::class, ConfirmsReceipt::class);
        $sut->handle(DomainMessage::anonMessage('id', new \stdClass()));

        self::assertTrue($handler->handled, 'Handler did not get the message');
    }

    public function testHandle() : void
    {
        $handler = new ConfirmsReceipt();
        $handler2 = new ConfirmsReceipt();

        $container = new class (['1' => $handler, '2' => $handler2]) implements ContainerInterface {
            private $handlers;

            public function __construct($handlers)
            {
                $this->handlers = $handlers;
            }

            public function get($id)
            {
                return $this->handlers[$id];
            }

            public function has($id)
            {
                return array_key_exists($id, $this->handlers);
            }
        };

        $sut = new Lazy([\stdClass::class => ['1'], \RuntimeException::class => ['2']], $container);
        $sut->handle(DomainMessage::anonMessage('id', new \stdClass()));

        self::assertTrue($handler->handled, 'Handler did not get the message');
        self::assertFalse($handler2->handled, 'Handler got a message it did not subscribe to');
    }
}

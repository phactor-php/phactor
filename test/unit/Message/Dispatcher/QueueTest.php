<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use Phactor\Message\Dispatcher\InMemory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Phactor\DomainMessage;
use Phactor\Message\Dispatcher\Queue;
use Phactor\Message\Handler;

/**
 * @covers \Phactor\Message\Dispatcher\Queue
 * @uses \Phactor\DomainMessage
 * @uses \Phactor\Message\Dispatcher\InMemory
 */
class QueueTest extends TestCase
{
    /**
     * This test works by redispatching a message after incrementing a counter inside it. If the queue works correctly, the
     * count should not change within the handle method of the testing handler, however if the queue class doesn't queue up
     * the message, the newly dispatched message will be handled inline, so by the time the assertion is reached at the end
     * of the first message handling, the count will have changed: failing the test.
     */
    public function testHandle() : void
    {
        $handler = new InMemory([]);
        $sut = new Queue($handler);

        $testingHandler =  new class ($sut, function ($expected, $actual) {self::assertEquals($expected, $actual);}) implements Handler {
            private $queue;
            private $assertion;

            public function __construct(Handler $queue, callable $assertion)
            {
                $this->queue = $queue;
                $this->assertion = $assertion;
            }

            public function handle(DomainMessage $domainMessage): void
            {
                $message = $domainMessage->getMessage();
                $message->count++;
                $count = $message->count;
                if ($message->count < 2) {
                    $this->queue->handle($domainMessage);
                }

                ($this->assertion)($count, $message->count);
            }
        };

        $handler->subscribe(\stdClass::class, $testingHandler);

        $message = new \stdClass();
        $message->count = 0;
        $sut->handle(DomainMessage::anonMessage('id', $message));
    }
}

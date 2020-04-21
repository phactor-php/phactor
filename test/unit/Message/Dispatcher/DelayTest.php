<?php

declare (strict_types=1);

namespace PhactorTest\Message\Dispatcher;

use Phactor\Actor\ActorIdentity;
use Phactor\DomainMessage;
use Phactor\EventStore\InMemoryEventStore;
use Phactor\ReadModel\InMemoryRepository;
use PhactorTestMocks\ConfirmsReceipt;
use PHPUnit\Framework\TestCase;
use Phactor\Message\Dispatcher\Delay;

/**
 * @covers \Phactor\Message\Dispatcher\Delay
 * @uses \Phactor\DomainMessage
 * @uses \Phactor\EventStore\InMemoryEventStore
 * @uses \Phactor\ReadModel\InMemoryRepository
 * @uses \Phactor\Actor\ActorIdentity
 * @uses \Phactor\Message\Dispatcher\Delay\DeferredMessage
 */
class DelayTest extends TestCase
{
    public function testHandle() : void
    {
        $handler = new ConfirmsReceipt();
        $sut = new Delay($handler, new InMemoryRepository(), new InMemoryEventStore());

        $sut->handle(DomainMessage::anonMessage('id', new \stdClass()));

        self::assertTrue($handler->handled, 'Message not received by handler');
    }

    public function testHandleFutureMessage() : void
    {
        $handler = new ConfirmsReceipt();
        $sut = new Delay($handler, new InMemoryRepository(), new InMemoryEventStore());

        $sut->handle(DomainMessage::recordFutureMessage(
            'id',
            (new \DateTime())->add(new \DateInterval('P1D')),
            null,
            new ActorIdentity('class', 'id'),
            1,
            new \stdClass()
        ));

        self::assertFalse($handler->handled, 'Message received by handler incorrectly');
    }

    public function testProcessMessages()
    {
        $actorIdentity = new ActorIdentity('class', 'id');
        $futureMessage = DomainMessage::recordFutureMessage(
            '1',
            (new \DateTime())->add(new \DateInterval('P1D')),
            null,
            $actorIdentity,
            2,
            new \stdClass()
        );

        $pastMessage = DomainMessage::recordFutureMessage(
            '2',
            (new \DateTime())->sub(new \DateInterval('P1D')),
            null,
            $actorIdentity,
            1,
            new \stdClass()
        );

        $repository = new InMemoryRepository();
        $repository->add(new Delay\DeferredMessage($futureMessage));
        $repository->add(new Delay\DeferredMessage($pastMessage));

        $eventStore = new InMemoryEventStore();
        $eventStore->save($actorIdentity, $pastMessage, $futureMessage);

        $handler = new ConfirmsReceipt();

        $sut = new Delay($handler, $repository, $eventStore);
        $sut->processMessages();

        self::assertEquals(1, $handler->count);
    }
}

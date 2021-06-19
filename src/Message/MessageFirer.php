<?php


namespace Phactor\Message;


use Phactor\DomainMessage;
use Phactor\Identity\Generator;
use Phactor\Message\Dispatcher\Capture;

class MessageFirer implements FiresMessages
{
    private Generator $identityGenerator;
    private Handler $wrappedHandler;

    public function __construct(Generator $identityGenerator, Handler $wrappedHandler)
    {
        $this->identityGenerator = $identityGenerator;
        $this->wrappedHandler = $wrappedHandler;
    }

    public function fire(object $message): array
    {
        if (!($this->wrappedHandler instanceof Capture)) {
            throw new \RuntimeException('Cannot use fire unless the passed handler is a capturing handler.');
        }

        $correlationId = $this->identityGenerator->generateIdentity();
        $domainMessage = DomainMessage::anonMessage($correlationId, $message);

        $this->wrappedHandler->handle($domainMessage);

        $messages = $this->wrappedHandler->capturedMessages();
        $this->wrappedHandler->reset();

        return $messages;
    }

    public function fireAndForget(object $message): void
    {
        $correlationId = $this->identityGenerator->generateIdentity();
        $domainMessage = DomainMessage::anonMessage($correlationId, $message);
        $this->wrappedHandler->handle($domainMessage);
    }
}

<?php


namespace Phactor\Message;


use Phactor\Identity\Generator;

class MessageFirer implements FiresMessages
{
    private $identityGenerator;
    private $wrappedBus;

    public function __construct(Generator $identityGenerator, Bus $wrappedBus)
    {
        $this->identityGenerator = $identityGenerator;
        $this->wrappedBus = $wrappedBus;
    }

    public function fire(object $message): array
    {
        $catcher = new class() implements Handler
        {
            public $messages;
            public function handle(DomainMessage $message)
            {
                $this->messages[] = $message;
            }
        };

        $correlationId = $this->identityGenerator->generateIdentity();
        $domainMessage = DomainMessage::anonMessage($correlationId, $message);

        $this->wrappedBus->subscribe($correlationId, $catcher);

        $this->wrappedBus->handle($domainMessage);

        return $catcher->messages;
    }

    public function fireAndForget(object $message): void
    {
        $correlationId = $this->identityGenerator->generateIdentity();
        $domainMessage = DomainMessage::anonMessage($correlationId, $message);
        $this->wrappedBus->handle($domainMessage);
    }
}
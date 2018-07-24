<?php


namespace Carnage\Phactor\Test;


use Carnage\Phactor\Identity\YouTubeStyleIdentityGenerator;
use Carnage\Phactor\Message\DomainMessage;
use Carnage\Phactor\Message\GenericBus;
use Carnage\Phactor\Message\GenericHandler;
use Carnage\Phactor\Message\Handler;
use Carnage\Phactor\Persistence\ActorRepository;
use Carnage\Phactor\Persistence\InMemoryEventStore;
use Carnage\Phactor\Zend\MessageHandlerManager;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;

class Helper
{
    private $messageBus;
    private $handler;

    private $generator;

    public function __construct($for)
    {
        $this->generator = new YouTubeStyleIdentityGenerator();
        $this->messageBus = new GenericBus(new Logger(new Noop()), [], new MessageHandlerManager(), $this->generator);

        if ($for instanceof Handler) {
            $this->handler = $for;
            return;
        }

        $repository = new ActorRepository($this->messageBus, new InMemoryEventStore(), $this->generator);
        $this->handler = new GenericHandler($for, $repository);
    }

    public function given(array $messages)
    {
        foreach ($messages as $message) {
            $domainMessage = $this->prepareMessage($message);
            $this->messageBus->handle($domainMessage);
        }
    }

    public function when($message)
    {

    }

    private function prepareMessage($messageOrDomainMessage)
    {
        if ($messageOrDomainMessage instanceof DomainMessage) {
            $message = $messageOrDomainMessage->getMessage();
            $domainMessage = $messageOrDomainMessage;
        } else {
            $message = $messageOrDomainMessage;
            $domainMessage = DomainMessage::anonMessage($this->generator->generateIdentity(), $message);
        }

        $this->messageBus->subscribe(\get_class($message), $this->handler);

        return $domainMessage;
    }
}
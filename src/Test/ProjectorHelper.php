<?php

namespace Phactor\Test;

use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\ActorIdentity;
use Phactor\Message\DomainMessage;
use Phactor\Message\GenericBus;
use Phactor\Message\GenericHandler;
use Phactor\Message\MessageFirer;
use Phactor\Persistence\ActorRepository;
use Phactor\Persistence\InMemoryEventStore;
use Phactor\ReadModel\InMemoryRepository;
use Phactor\Zend\MessageHandlerManager;
use PHPUnit\Framework\Assert;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;

class ProjectorHelper
{
    private $messageBus;
    private $handler;
    private $repository;
    private $projectorClass;

    private $triggeredMessages;
    private $messageFirer;

    public function __construct($for)
    {
        $this->projectorClass = $for;
        $this->repository = new InMemoryRepository();
        $this->handler = new $for($this->repository);

        $anonIdentityGenerator = new YouTubeStyleIdentityGenerator();
        $this->messageBus = new GenericBus((new Logger())->addWriter(new Noop()), [], new MessageHandlerManager(), $anonIdentityGenerator);
        $this->messageFirer = new MessageFirer($anonIdentityGenerator, $this->messageBus);
    }

    public function given($entity)
    {
        $this->repository->add($entity);
    }

    public function when($message)
    {
        $this->messageBus->subscribe(\get_class($message), $this->handler);
        $this->triggeredMessages = $this->stripMessages($this->messageFirer->fire($message));

        return $this;
    }

    public function expect($entity)
    {
        Assert::assertEquals($entity, $this->repository->get(0));
    }

    private function stripMessages(array $messages): array
    {
        return  \array_map(function (DomainMessage $domainMessage) {
            return $domainMessage->getMessage();
        }, $messages);
    }
}
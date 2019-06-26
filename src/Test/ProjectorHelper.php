<?php

namespace Phactor\Test;

use Phactor\Identity\YouTubeStyleIdentityGenerator;
use Phactor\Message\DomainMessage;
use Phactor\Message\GenericBus;
use Phactor\Message\Handler;
use Phactor\Message\MessageFirer;
use Phactor\ReadModel\InMemoryRepository;
use Phactor\ReadModel\Repository;
use Phactor\Zend\MessageHandlerManager;
use PHPUnit\Framework\Assert;
use Zend\Log\Logger;
use Zend\Log\Writer\Noop;

class ProjectorHelper
{
    private $messageBus;
    private $handler;
    /** @var Repository */
    private $repository;

    private $triggeredMessages;
    private $messageFirer;

    private function __construct(Handler $handler)
    {
        $this->handler = $handler;

        $anonIdentityGenerator = new YouTubeStyleIdentityGenerator();
        $this->messageBus = new GenericBus((new Logger())->addWriter(new Noop()), [], new MessageHandlerManager());
        $this->messageFirer = new MessageFirer($anonIdentityGenerator, $this->messageBus);
    }

    public static function fromClassName(string $className): ProjectorHelper
    {
        $repository = new InMemoryRepository();
        $handler =  new $className($repository);

        $instance = new static($handler);
        $instance->repository = $repository;
        return $instance;
    }

    public static function fromFactory(callable $factory): ProjectorHelper
    {
        $repository = new InMemoryRepository();
        $handler =  $factory($repository);

        $instance = new static($handler);
        $instance->repository = $repository;
        return $instance;
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
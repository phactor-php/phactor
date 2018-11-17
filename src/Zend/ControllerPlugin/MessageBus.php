<?php

namespace Phactor\Zend\ControllerPlugin;

use Phactor\Message\DomainMessage;
use Phactor\Message\FiresMessages;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class MessageBus extends AbstractPlugin
{
    private $messageBus;

    public function __construct(FiresMessages $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    public function __invoke()
    {
        return $this;
    }

    public function fire($message): array
    {
        return $this->messageBus->fire($message);
    }

    public function firstInstanceOf(string $messageType, DomainMessage ... $domainMessages): object
    {
        foreach ($domainMessages as $domainMessage) {
            if ($domainMessage->getMessage() instanceof $messageType) {
                return $domainMessage->getMessage();
            }
        }

        throw new \RuntimeException('Message type not found');
    }
}
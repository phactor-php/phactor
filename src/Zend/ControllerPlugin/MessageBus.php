<?php

namespace Carnage\Phactor\Zend\ControllerPlugin;

use Carnage\Phactor\Message\Bus;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class MessageBus extends AbstractPlugin
{
    private $messageBus;

    public function __construct(Bus $messageBus)
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
}
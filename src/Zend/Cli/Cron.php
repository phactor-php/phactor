<?php

namespace Carnage\Phactor\Zend\Cli;

use Carnage\Phactor\Message\DelayedMessage\DelayedMessageBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Cron extends Command
{
    /**
     * @var DelayedMessageBus
     */
    private $delayedMessageBus;

    public static function build(DelayedMessageBus $delayedMessageBus)
    {
        $instance = new static();
        $instance->delayedMessageBus = $delayedMessageBus;

        return $instance;
    }

    protected function configure()
    {
        $this->setName('phactor:cron')
            ->setDescription('Runs any delayed messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->delayedMessageBus->processMessages();
    }
}

<?php

namespace Carnage\Phactor\Zend\ControllerPlugin;

use Carnage\Phactor\Message\Bus;
use Carnage\Phactor\Zend\RepositoryManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Repository extends AbstractPlugin
{
    private $repositoryManager;

    public function __construct(RepositoryManager $repositoryManager)
    {
        $this->repositoryManager = $repositoryManager;
    }

    public function __invoke(string $className)
    {
        return $this->repositoryManager->get($className);
    }
}
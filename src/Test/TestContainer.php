<?php

namespace Phactor\Test;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TestContainer implements ContainerInterface
{
    private $services;
    private $factories;

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new class implements NotFoundExceptionInterface {};
        }

        if (!isset($this->services[$id])) {
            $this->services[$id] = $this->factories[$id]();
        }

        return $this->services[$id];
    }

    public function has($id)
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }

    public function setService($id, $service)
    {
        $this->services[$id] = $service;
    }

    public function setFactory($id, $service): void
    {
        $this->factories[$id] = $service;
    }
}
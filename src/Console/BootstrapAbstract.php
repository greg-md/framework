<?php

namespace Greg\Framework\Console;

use Greg\Framework\Application;
use Greg\Framework\BootingTrait;
use Greg\Framework\IoCContainer;

abstract class BootstrapAbstract implements BootstrapStrategy
{
    use BootingTrait;

    private $kernel;

    public function kernel(): ConsoleKernel
    {
        if (!$this->kernel) {
            throw new \Exception('Kernel not defined in http bootstrap.');
        }

        return $this->kernel;
    }

    public function app(): Application
    {
        return $this->kernel()->app();
    }

    public function ioc(): IoCContainer
    {
        return $this->app()->ioc();
    }

    public function console(): \Symfony\Component\Console\Application
    {
        return $this->kernel()->console();
    }

    public function boot(ConsoleKernel $kernel)
    {
        $this->kernel = $kernel;

        return $this->bootstrap();
    }

    protected function booting(string $method)
    {
        return $this->kernel()->app()->ioc()->call([$this, $method]);
    }
}

<?php

namespace Greg\Framework\Http;

use Greg\DependencyInjection\IoCContainer;
use Greg\Framework\Application;
use Greg\Framework\BootingTrait;
use Greg\Routing\Router;

abstract class BootstrapAbstract implements BootstrapStrategy
{
    use BootingTrait;

    private $kernel;

    public function kernel(): HttpKernel
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

    public function router(): Router
    {
        return $this->kernel()->router();
    }

    public function boot(HttpKernel $kernel)
    {
        $this->kernel = $kernel;

        return $this->bootstrap();
    }

    protected function booting(string $method)
    {
        return $this->kernel()->app()->ioc()->call([$this, $method]);
    }
}

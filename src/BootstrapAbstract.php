<?php

namespace Greg\Framework;

abstract class BootstrapAbstract implements BootstrapStrategy
{
    use BootingTrait;

    private $application;

    public function app(): Application
    {
        if (!$this->application) {
            throw new \Exception('Application not defined in bootstrap.');
        }

        return $this->application;
    }

    public function ioc(): IoCContainer
    {
        return $this->app()->ioc();
    }

    public function boot(Application $kernel)
    {
        $this->application = $kernel;

        return $this->bootstrap();
    }

    protected function booting(string $method)
    {
        return $this->app()->ioc()->call([$this, $method]);
    }
}

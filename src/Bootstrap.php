<?php

namespace Greg\Framework;

use Greg\Support\Str;

abstract class Bootstrap implements BootstrapStrategy
{
    private $application;

    private $booted = [];

    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function app(): Application
    {
        if (!$this->application) {
            throw new \Exception('Application not defined in bootstrap.');
        }

        return $this->application;
    }

    public function boot(Application $application)
    {
        $this->setApplication($application);

        // Call all methods which starts with "boot"
        foreach (get_class_methods($this) as $method) {
            if ($method === 'boot') {
                continue;
            }

            if (Str::startsWith($method, 'boot') and mb_strtoupper($method[4]) === $method[4]) {
                $dependency = lcfirst(mb_substr($method, 4));

                if (in_array($dependency, $this->booted)) {
                    continue;
                }

                $this->app()->ioc()->call([$this, $method]);

                $this->booted[] = $dependency;
            }
        }

        return $this;
    }

    public function dependsOn(string ...$dependencies)
    {
        foreach ($dependencies as $dependency) {
            if (in_array($dependency, $this->booted)) {
                continue;
            }

            if (!method_exists($this, $method = 'boot' . ucfirst($dependency))) {
                throw new \Exception('Bootable dependency `' . $dependency . '` does not exists.');
            }

            $this->app()->ioc()->call([$this, $method]);

            $this->booted[] = $dependency;
        }

        return $this;
    }

    public function booted(): array
    {
        return $this->booted;
    }
}

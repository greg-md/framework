<?php

namespace Greg\Framework;

trait BootstrapTrait
{
    private $bootstraps = [];

    public function getBootstrap(string $name)
    {
        return $this->bootstraps[$name] ?? null;
    }

    public function getBootstraps()
    {
        return $this->bootstraps;
    }

    private function setBootstrap($class)
    {
        $this->bootstraps[get_class($class)] = $class;

        return $this;
    }
}
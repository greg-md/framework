<?php

namespace Greg\Framework;

use Greg\Framework\Console\ConsoleKernel;
use Greg\Framework\Http\HttpKernel;

abstract class ServiceProvider
{
    abstract public function name();

    public function boot(Application $app)
    {
    }

    public function bootConsoleKernel(ConsoleKernel $kernel)
    {
    }

    public function bootHttpKernel(HttpKernel $kernel)
    {
    }

    public function install()
    {
    }
}

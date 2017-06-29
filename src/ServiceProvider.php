<?php

namespace Greg\Framework;

use Greg\Framework\Console\ConsoleKernel;
use Greg\Framework\Http\HttpKernel;

abstract class ServiceProvider
{
    public function boot(Application $app)
    {
    }

    public function bootConsoleKernel(ConsoleKernel $kernel)
    {
    }

    public function bootHttpKernel(HttpKernel $kernel)
    {
    }
}

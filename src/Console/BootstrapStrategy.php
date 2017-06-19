<?php

namespace Greg\Framework\Console;

interface BootstrapStrategy
{
    public function boot(ConsoleKernel $kernel);
}

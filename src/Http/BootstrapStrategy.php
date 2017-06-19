<?php

namespace Greg\Framework\Http;

interface BootstrapStrategy
{
    public function boot(HttpKernel $kernel);
}

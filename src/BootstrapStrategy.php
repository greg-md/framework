<?php

namespace Greg\Framework;

interface BootstrapStrategy
{
    public function boot(Application $application);
}

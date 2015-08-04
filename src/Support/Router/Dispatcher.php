<?php

namespace Greg\Support\Router;

use Greg\Support\Engine\InternalTrait;

class Dispatcher
{
    use RouterTrait, InternalTrait;

    public function __construct(array $routes = [])
    {
        $this->addMore($routes);

        return $this;
    }
}
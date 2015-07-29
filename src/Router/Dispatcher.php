<?php

namespace Greg\Router;

class Dispatcher extends Router
{
    public function __construct(array $routes = [])
    {
        $this->addMore($routes);

        return $this;
    }

    static public function create($appName, array $routes = [])
    {
        return static::newInstanceRef($appName, $routes);
    }
}

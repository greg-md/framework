<?php

namespace Greg\Router;

use Greg\Engine\Internal;
use Greg\Support\Arr;

class Dispatcher
{
    use Internal;

    public $routes = [];

    public function __construct(array $routes = [])
    {
        $this->addMore($routes);

        return $this;
    }

    static public function create($appName, array $routes = [])
    {
        return static::newInstanceRef($appName, $routes);
    }

    public function addMore(array $routes)
    {
        foreach($routes as $route) {
            $this->any(...Arr::bring($route));
        }

        return $this;
    }

    public function any($name, $format, $settings = null)
    {
        $route = new Route($format);

        if (is_callable($settings)) {
            $route->callback($settings);
        }

        if (is_array($settings)) {
            foreach($settings as $key => $value) {
                switch($key) {
                    case 'strict':
                    case 'callback':
                        $route->$key($value);

                        break;
                }
            }
        }

        $this->routes[$name] = $route;

        return $route;
    }

    public function dispatch($path)
    {
        /* @var $route Route */
        foreach($this->routes as $name => $route) {
            $data = $route->dispatch($path);

            if ($data !== false) {
                return $data;
            }
        }

        return null;
    }
}

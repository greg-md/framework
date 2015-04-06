<?php

namespace Greg\Router;

use Greg\Engine\Internal;
use Greg\Support\Arr;

class Dispatcher
{
    use Internal;

    const EVENT_DISPATCH = 'router.dispatch';

    const EVENT_DISPATCHING = 'router.dispatching';

    const EVENT_DISPATCHED = 'router.dispatched';

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
        $route = Route::create($this->appName(), $name, $format);

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

    public function dispatch($path, &$foundRoute = null)
    {
        $this->app()->listener()->fireRef(static::EVENT_DISPATCH, $path);

        $content = null;

        /* @var $route Route */
        foreach($this->routes as $name => $route) {
            if ($route->match($path)) {
                $foundRoute = $route;

                $this->app()->listener()->fire(static::EVENT_DISPATCHING, $route);

                $content = $route->dispatch();

                $this->app()->listener()->fire(static::EVENT_DISPATCHED, $route);

                break;
            }
        }

        return $content;
    }
}

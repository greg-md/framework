<?php

namespace Greg\Router;

use Greg\Support\Arr;
use Greg\Engine\InternalTrait;
use Greg\Support\Str;

abstract class Router
{
    use InternalTrait;

    const EVENT_DISPATCH = 'router.dispatch';

    const EVENT_DISPATCHING = 'router.dispatching';

    const EVENT_DISPATCHED = 'router.dispatched';

    /**
     * @var Route[]
     */
    public $routes = [];

    public function addMore(array $routes)
    {
        foreach($routes as $args) {
            $this->any(...Arr::bring($args));
        }

        return $this;
    }

    public function any($name, $format, $settings = null)
    {
        return $this->setRoute($name, $format, null, $settings);
    }

    public function post($name, $format, $settings = null)
    {
        return $this->setRoute($name, $format, Route::TYPE_POST, $settings);
    }

    public function get($name, $format, $settings = null)
    {
        return $this->setRoute($name, $format, Route::TYPE_GET, $settings);
    }

    public function group($name, $format, callable $callable, $settings = null)
    {
        $route = $this->setRoute($name, $format, Route::TYPE_GROUP, $settings)->strict(false);

        $this->app()->binder()->callWith($callable, $route);

        return $route;
    }

    public function setRoute($name, $format, $type = null, $settings = null)
    {
        $this->routes[$name] = $route = $this->createRoute($name, $format, $type, $settings);

        return $route;
    }

    public function hasRoute($name)
    {
        if (array_key_exists($name, $this->routes)) {
            return true;
        }

        foreach($this->routes as $route) {
            if ($route->hasRoute($name)) {
                return true;
            }
        }

        return false;
    }

    public function getRoute($name)
    {
        if (array_key_exists($name, $this->routes)) {
            return $this->routes[$name];
        }

        foreach($this->routes as $route) {
            if ($r = $route->getRoute($name)) {
                return $r;
            }
        }

        throw new \Exception('Route `' . $name . '` not found.');
    }

    /**
     * @param string $name
     * @param string $format
     * @param null $type
     * @param callable|array $settings
     * @return Route
     */
    public function createRoute($name, $format, $type = null, $settings = null)
    {
        $route = Route::create($this->appName(), $name, $format, $type);

        if (is_callable($settings)) {
            $route->callback($settings);
        }

        if (Str::isScalar($settings)) {
            $settings = ['action' => $settings];
        }

        if (is_array($settings)) {
            foreach($settings as $key => $value) {
                switch($key) {
                    case 'strict':
                    case 'callback':
                    case 'encodeValues':
                    case 'action':
                    case 'defaults':
                        $route->$key($value);

                        break;
                }
            }
        }

        return $route;
    }

    public function dispatchPath($path, array $events = [], &$foundRoute = null)
    {
        $listener = $this->app()->listener();

        $listener->fireRef(static::EVENT_DISPATCH, $path);

        if (Arr::has($events, static::EVENT_DISPATCH)) {
            $listener->fireRef($events[static::EVENT_DISPATCH], $path);
        }

        $content = null;

        foreach($this->routes as $route) {
            if ($matchedRoute = $route->match($path)) {
                $foundRoute = $matchedRoute;

                $listener->fireWith(static::EVENT_DISPATCHING, $matchedRoute);

                if (Arr::has($events, static::EVENT_DISPATCHING)) {
                    $listener->fireWith($events[static::EVENT_DISPATCHING], $matchedRoute);
                }

                $content = $matchedRoute->dispatch();

                $listener->fireWith(static::EVENT_DISPATCHED, $matchedRoute);

                if (Arr::has($events, static::EVENT_DISPATCHED)) {
                    $listener->fireWith($events[static::EVENT_DISPATCHED], $matchedRoute);
                }

                break;
            }
        }

        return $content;
    }

    public function fetchRoute($routeName, array $params = [], $full = false)
    {
        return $this->getRoute($routeName)->fetch($params, $full);
    }
}
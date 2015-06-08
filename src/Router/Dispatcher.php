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

    /**
     * @var Route[]
     */
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

    public function set($name, $format, $type = null, $settings = null)
    {
        $route = $this->createRoute($name, $format, $type, $settings);

        $this->routes[$name] = $route;

        return $route;
    }

    /**
     * @param $name
     * @param $format
     * @param null $type
     * @param null $settings
     * @return Route
     */
    public function createRoute($name, $format, $type = null, $settings = null)
    {
        $route = Route::create($this->appName(), $name, $format, $type);

        if (is_callable($settings)) {
            $route->callback($settings);
        }

        if (is_scalar($settings)) {
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

    public function any($name, $format, $settings = null)
    {
        return $this->set($name, $format, null, $settings);
    }

    public function post($name, $format, $settings = null)
    {
        return $this->set($name, $format, 'post', $settings);
    }

    public function dispatch($path, array $events = [], &$foundRoute = null)
    {
        $listener = $this->app()->listener();

        $listener->fireRef(static::EVENT_DISPATCH, $path);

        if (Arr::has($events, static::EVENT_DISPATCH)) {
            $listener->fireRef($events[static::EVENT_DISPATCH], $path);
        }

        $content = null;

        foreach($this->routes as $name => $route) {
            if ($route->match($path)) {
                $foundRoute = $route;

                $listener->fire(static::EVENT_DISPATCHING, $route);

                if (Arr::has($events, static::EVENT_DISPATCHING)) {
                    $listener->fireRef($events[static::EVENT_DISPATCHING], $route);
                }

                $content = $route->dispatch();

                $listener->fire(static::EVENT_DISPATCHED, $route);

                if (Arr::has($events, static::EVENT_DISPATCHED)) {
                    $listener->fireRef($events[static::EVENT_DISPATCHED], $route);
                }

                break;
            }
        }

        return $content;
    }

    public function fetch($routeName, array $params = [], $full = false)
    {
        return $this->get($routeName)->fetch($params, $full);
    }

    public function get($name)
    {
        if (!$this->has($name)) {
            throw new \Exception('Route `' . $name . '` not found.');
        }

        return $this->routes[$name];
    }

    public function has($name)
    {
        return array_key_exists($name, $this->routes);
    }
}

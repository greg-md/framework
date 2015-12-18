<?php

namespace Greg\Router;

use Greg\Tool\Arr;
use Greg\Tool\Obj;
use Greg\Tool\Str;

trait RouterTrait
{
    /**
     * @var Route[]
     */
    protected $routes = [];

    protected $onError = [];

    protected $binders = [];

    protected $boundParams = [];

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
        $route = $this->setRoute($name, $format, Route::TYPE_GROUP, $settings);

        $route->strict(false);

        $this->callCallableWith($callable, $route);

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
            if ($subRoute = $route->getRoute($name)) {
                return $subRoute;
            }
        }

        throw new \Exception('Route `' . $name . '` not found.');
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function createRoute($name, $format, $type = null, $settings = null)
    {
        return $this->_createRoute($name, $format, $type, $settings);
    }

    /**
     * @param string $name
     * @param string $format
     * @param null $type
     * @param callable|array $settings
     * @return Route
     */
    protected function _createRoute($name, $format, $type = null, $settings = null)
    {
        $route = $this->newRoute($name, $format, $type);

        $route->onError($this->onError());

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
                    case 'onError':
                        $route->$key($value);

                        break;
                }
            }
        }

        return $route;
    }

    protected function newRoute($name, $format, $type = null)
    {
        return new Route($name, $format, $type);
    }

    public function dispatchPath($path, &$foundRoute = null)
    {
        foreach($this->routes as $route) {
            if ($matchedRoute = $route->match($path)) {
                $foundRoute = $matchedRoute;

                return $matchedRoute->dispatch();
            }
        }

        return null;
    }

    public function fetchRoute($routeName, array $params = [], $full = false)
    {
        return $this->getRoute($routeName)->fetch($params, $full);
    }

    public function bind($name, callable $result)
    {
        return $this->binders($name, $result);
    }

    public function bindParams(array $params)
    {
        foreach($params as $key => &$value) {
            $value = $this->getBoundParam($key, $params);
        }
        unset($value);

        return $params;
    }

    public function getBoundParam($key, array $params)
    {
        if (Arr::has($this->boundParams, $key)) {
            return $this->boundParams($key);
        }

        if ($binder = $this->binders($key)) {
            $value = $this->callCallable($binder, $params);

            $this->boundParams($key, $value);
        } else {
            $value = Arr::get($params, $key);
        }

        return $value;
    }

    public function onError($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function binders($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function boundParams($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    abstract protected function callCallable(callable $callable, ...$args);

    abstract protected function callCallableWith(callable $callable, ...$args);
}
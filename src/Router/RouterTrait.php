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

    public function any($format, $action, array $settings = [])
    {
        $settings['type'] = null;

        return $this->setRoute($format, $action, $settings);
    }

    public function post($format, $action, array $settings = [])
    {
        $settings['type'] = Route::TYPE_POST;

        return $this->setRoute($format, $action, $settings);
    }

    public function get($format, $action, array $settings = [])
    {
        $settings['type'] = Route::TYPE_GET;

        return $this->setRoute($format, $action, $settings);
    }

    public function group($format, callable $callable, array $settings = [])
    {
        $settings['type'] = Route::TYPE_GROUP;

        $route = $this->setRoute($format, null, $settings);

        $route->strict(false);

        $this->callCallableWith($callable, $route);

        return $route;
    }

    public function setRoute($format, $action, $settings = null)
    {
        $this->routes[] = $route = $this->createRoute($format, $action, $settings);

        return $route;
    }

    public function hasRoute($name)
    {
        foreach($this->routes as $route) {
            if ($routeName = $route->name() and $routeName == $name) {
                return true;
            }

            if ($route->hasRoute($name)) {
                return true;
            }
        }

        return false;
    }

    public function getRoute($name)
    {
        foreach($this->routes as $route) {
            if ($routeName = $route->name() and $routeName == $name) {
                return $route;
            }

            if ($route->hasRoutes() and $subRoute = $route->getRoute($name)) {
                return $subRoute;
            }
        }

        throw new \Exception('Route `' . $name . '` not found.');
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function hasRoutes()
    {
        return $this->routes ? true : false;
    }

    public function createRoute($format, $action, array $settings = [])
    {
        return $this->_createRoute($format, $action, $settings);
    }

    protected function _createRoute($format, $action, array $settings = [])
    {
        $route = $this->newRoute($format, $action, $settings);

        $route->onError($this->onError());

        return $route;
    }

    protected function newRoute($format, $action, array $settings = [])
    {
        return new Route($format, $action, $settings);
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
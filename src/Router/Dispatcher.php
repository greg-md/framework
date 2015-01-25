<?php

namespace Greg\Router;

use Greg\Engine\Internal;
use Greg\Router\Route\Normal;
use Greg\Support\Obj;

class Dispatcher
{
    use Internal;

    public $path = '/';

    public $route = null;

    public $param = [];

    public $routes = [];

    public function fetch($path)
    {
        $this->path($path);

        $route = null;

        foreach($this->routes() as $name => $info) {
            $route = $this->newRouter($name, $info);
            $param = $route->fetch($path);
            if ($param !== false) {
                $this->route($route);
                $this->param($param);
                break;
            }
        }

        if (!$route) {
            $this->param(Route::pathToParam($path));
        }

        return $this;
    }

    public function add($name, $format, $options = null)
    {
        return $this->routes($name, [
            'type' => 'normal',
            'format' => $format,
            'options' => $options,
        ]);
    }

    protected function newRouter($name, $info)
    {
        switch($info['type']) {
            case 'normal':
                return Normal::create($this->appName(), $name, $info['format'], $info['options']);
        }

        throw Exception::create($this->appName(), 'Wrong type of router `' . $name . '`');
    }

    public function dispatch()
    {
        $route = $this->route();

        if ($route) {
            $callback = $route->callback();
            if ($callback) {
                $data = $callback($this->param());
            } else {
                $data = $this->app()->action($this->param('action'), $this->param('controller'), $this->param());
            }
        } else {
            $data = $this->app()->action($this->param('action'), $this->param('controller'), $this->param());
        }

        return $data;
    }

    public function path($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    /**
     * @param Route $value
     * @return Route|$this|null
     */
    public function route(Route $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function param($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function routes($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}

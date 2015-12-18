<?php

namespace Greg\Application\View;

use Greg\Application\Engine\InternalTrait;
use Greg\Application\Http\Response;

class Viewer extends \Greg\View\Viewer
{
    use InternalTrait;

    static public function create($appName, $paths = [], array $param = [])
    {
        return static::newInstanceRef($appName, $paths, $param);
    }

    protected function newResponse($content)
    {
        return Response::create($this->appName(), $content);
    }

    public function __get($key)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        return $this->app()->get($key);
    }

    public function __call($method, array $args = [])
    {
        $components = $this->app()->components();

        if ($components->has($method)) {
            return $components->get($method);
        }

        throw new \Exception('Component `' . $method . '` not found.');
    }
}
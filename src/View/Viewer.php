<?php

namespace Greg\View;

use Greg\Engine\InternalTrait;
use Greg\Http\Response;

class Viewer extends \Greg\Support\View\Viewer
{
    use InternalTrait;

    static public function create($appName, $paths = [], array $param = [])
    {
        return static::newInstanceRef($appName, $paths, $param);
    }

    protected function createResponse($content)
    {
        return Response::create($this->appName(), $content);
    }

    protected function callCompiler(callable $compiler)
    {
        return $this->app()->binder()->call($compiler);
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
<?php

namespace Greg\Http;

use Greg\Engine\InternalTrait;

class Response extends \Greg\Support\Http\Response
{
    use InternalTrait;

    /**
     * @param $appName
     * @param null $content
     * @param null $contentType
     * @return self
     * @throws \Exception
     */
    static public function create($appName, $content = null, $contentType = null)
    {
        return static::newInstanceRef($appName, $content, $contentType);
    }

    public function route($name, array $params = [], $code = null)
    {
        $this->location($this->app()->router()->fetchRoute($name, $params));

        if ($code !== null) {
            $this->code($code);
        }

        return $this;
    }

    protected function callCallback(callable $callback)
    {
        return $this->app()->binder()->callWith($callback, $this);
    }
}
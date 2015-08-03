<?php

namespace Greg\Cache\Storage;

use Greg\Engine\InternalTrait;

class Redis extends \Greg\Support\Cache\Storage\Redis
{
    use InternalTrait;

    static public function create($appName, $host = null, $port = null, $prefix = null, $timeout = null)
    {
        return static::newInstanceRef($appName, $host, $port, $prefix, $timeout);
    }

    protected function callCallable(callable $callable)
    {
        return $this->app()->binder()->call($callable);
    }
}
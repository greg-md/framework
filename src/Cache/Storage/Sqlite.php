<?php

namespace Greg\Cache\Storage;

use Greg\Engine\InternalTrait;

class Sqlite extends \Greg\Support\Cache\Storage\Sqlite
{
    use InternalTrait;

    static public function create($appName, $path)
    {
        return static::newInstanceRef($appName, $path);
    }

    protected function callCallable(callable $callable)
    {
        return $this->app()->binder()->call($callable);
    }
}
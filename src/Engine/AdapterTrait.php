<?php

namespace Greg\Engine;

use Greg\Application\Runner;
use Greg\Support\Obj;
use Greg\Support\Str;

trait AdapterTrait
{
    protected $adapter = null;

    public function adapter($adapter = null)
    {
        return Obj::fetchCallableVar($this, $this->{__FUNCTION__}, function($adapter) {
            if (Str::isScalar($adapter)) {
                $adapter = [$adapter];
            }

            if (is_array($adapter)) {
                $adapter = $this->app()->loadInstance(...$adapter);
            }

            return $adapter;
        }, ...func_get_args());
    }

    public function __call($method, $args)
    {
        return $this->adapter()->$method(...$args);
    }

    /**
     * @param Runner $app
     * @return Runner
     */
    abstract public function app(Runner $app = null);
}
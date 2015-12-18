<?php

namespace Greg\Engine;

use Greg\Tool\Obj;
use Greg\Tool\Str;

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
                $adapter = $this->loadClassInstance(...$adapter);
            }

            return $adapter;
        }, ...func_get_args());
    }

    public function __call($method, $args)
    {
        return $this->adapter()->$method(...$args);
    }

    abstract protected function loadClassInstance($className, ...$args);
}
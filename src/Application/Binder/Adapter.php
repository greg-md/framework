<?php

namespace Greg\Application\Binder;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Adapter
{
    use Internal;

    protected $caller = null;

    public function __construct(callable $caller, callable $storageCaller = null)
    {
        $this->caller($caller);

        $this->storageCaller($storageCaller);
    }

    public function has($className)
    {
        return $this->app()->binder()->call($this->storageCaller(), $className);
    }

    static public function create($appName, callable $caller, array $storage = [])
    {
        return static::newInstanceRef($appName, $caller, $storage);
    }

    public function caller(callable $callable = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function storageCaller(callable $callable = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
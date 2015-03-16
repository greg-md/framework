<?php

namespace Greg\Application\Binder;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Adapter implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    protected $caller = null;

    public function __construct(callable $caller, array $storage = [])
    {
        $this->caller($caller);

        $this->storage($storage);
    }

    static public function create($appName, callable $caller, array $storage = [])
    {
        return static::newInstanceRef($appName, $caller, $storage);
    }

    public function caller(callable $callable = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
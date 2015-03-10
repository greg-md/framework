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

    public function __construct(callable $callable, array $storage = [])
    {
        $this->caller($callable);

        $this->storage = $storage;
    }

    public function caller($value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
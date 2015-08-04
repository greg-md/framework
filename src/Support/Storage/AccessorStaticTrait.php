<?php

namespace Greg\Support\Storage;

use Greg\Support\Tool\Obj;

trait AccessorStaticTrait
{
    static protected $storage = [];

    static protected function &storage($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar(true, static::$storage, ...func_get_args());
    }

    static protected function &accessor(array $storage = [])
    {
        return Obj::fetchVar(true, static::$storage, ...func_get_args());
    }
}
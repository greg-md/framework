<?php

namespace Greg\Storage;

use Greg\Support\Obj;

trait AccessorStatic
{
    static protected $storage = [];

    static protected function &accessor(array $accessor = [])
    {
        if (func_num_args()) {
            static::$storage = $accessor;

            return true;
        }

        return static::$storage;
    }
}
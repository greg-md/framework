<?php

namespace Greg\Storage;

use Greg\Support\Obj;

trait Accessor
{
    protected $storage = [];

    protected function &accessor(array $accessor = [])
    {
        return Obj::fetchVar($this, $this->storage, func_get_args());
    }
}
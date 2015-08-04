<?php

namespace Greg\Support\Storage;

use Greg\Support\Tool\Obj;

trait AccessorTrait
{
    protected $storage = [];

    protected function storage($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->storage, ...func_get_args());
    }

    protected function &accessor(array $storage = [])
    {
        return Obj::fetchVar($this, $this->storage, ...func_get_args());
    }
}
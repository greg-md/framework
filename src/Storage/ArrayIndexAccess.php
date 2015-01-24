<?php

namespace Greg\Storage;

use Greg\Support\Arr;

trait ArrayIndexAccess
{
    use ArrayAccess;

    public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas($this->storage, $index, $delimiter);
    }

    public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexSet($this->storage, $index, $value, $delimiter);
    }

    public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet($this->storage, $index, $else, $delimiter);
    }

    public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexDel($this->storage, $index, $delimiter);
    }
}
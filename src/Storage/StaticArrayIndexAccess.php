<?php

namespace Greg\Storage;

use Greg\Support\Arr;

trait StaticArrayIndexAccess
{
    use StaticArrayAccess;

    static public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas(static::$storage, $index, $delimiter);
    }

    static public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexSet(static::$storage, $index, $value, $delimiter);
    }

    static public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet(static::$storage, $index, $else, $delimiter);
    }

    static public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexDel(static::$storage, $index, $delimiter);
    }
}
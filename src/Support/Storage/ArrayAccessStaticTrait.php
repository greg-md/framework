<?php

namespace Greg\Support\Storage;

use Greg\Support\Arr;

trait ArrayAccessStaticTrait
{
    abstract protected function &accessor(array $storage = []);

    static public function has($key, ...$keys)
    {
        return Arr::has(static::accessor(), $key, ...$keys);
    }

    static public function hasIndex($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex(static::accessor(), $index, $delimiter);
    }

    static public function set($key, $value)
    {
        return Arr::set(static::accessor(), $key, $value);
    }

    static public function setRef($key, &$value)
    {
        return Arr::setRef(static::accessor(), $key, $value);
    }

    static public function setIndex($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex(static::accessor(), $index, $value, $delimiter);
    }

    static public function setIndexRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndexRef(static::accessor(), $index, $value, $delimiter);
    }

    static public function get($key, $else = null)
    {
        return Arr::get(static::accessor(), $key, $else);
    }

    static public function &getRef($key, $else = null)
    {
        return Arr::getRef(static::accessor(), $key, $else);
    }

    static public function &getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex(static::accessor(), $index, $else, $delimiter);
    }

    static public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef(static::accessor(), $index, $else, $delimiter);
    }

    static public function del($key, ...$keys)
    {
        return Arr::del(static::accessor(), $key, ...$keys);
    }

    static public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex(static::accessor(), $index, $delimiter);
    }
}
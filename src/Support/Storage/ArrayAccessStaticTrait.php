<?php

namespace Greg\Support\Storage;

use Greg\Support\Tool\Arr;

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
        return Arr::set(static::accessor(), $key, $value);
    }

    static public function setIndex($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex(static::accessor(), $index, $value, $delimiter);
    }

    static public function setIndexRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex(static::accessor(), $index, $value, $delimiter);
    }

    static public function get($key, $else = null)
    {
        return Arr::get(static::accessor(), $key, $else);
    }

    static public function &getRef($key, $else = null)
    {
        return Arr::getRef(static::accessor(), $key, $else);
    }

    static public function getForce($key, $else = null)
    {
        return Arr::getForce(static::accessor(), $key, $else);
    }

    static public function &getForceRef($key, $else = null)
    {
        return Arr::getForceRef(static::accessor(), $key, $else);
    }

    static public function getArray($key, $else = null)
    {
        return Arr::getArray(static::accessor(), $key, $else);
    }

    static public function &getArrayRef($key, $else = null)
    {
        return Arr::getArrayRef(static::accessor(), $key, $else);
    }

    static public function getArrayForce($key, $else = null)
    {
        return Arr::getArrayForce(static::accessor(), $key, $else);
    }

    static public function &getArrayForceRef($key, $else = null)
    {
        return Arr::getArrayForceRef(static::accessor(), $key, $else);
    }

    static public function getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex(static::accessor(), $index, $else, $delimiter);
    }

    static public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef(static::accessor(), $index, $else, $delimiter);
    }

    static public function getIndexForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce(static::accessor(), $index, $else, $delimiter);
    }

    static public function &getIndexForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef(static::accessor(), $index, $else, $delimiter);
    }

    static public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray(static::accessor(), $index, $else, $delimiter);
    }

    static public function &getIndexArrayRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayRef(static::accessor(), $index, $else, $delimiter);
    }

    static public function getIndexArrayForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForce(static::accessor(), $index, $else, $delimiter);
    }

    static public function &getIndexArrayForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForceRef(static::accessor(), $index, $else, $delimiter);
    }

    static public function required($key)
    {
        return Arr::required(static::accessor(), $key);
    }

    static public function &requiredRef($key)
    {
        return Arr::requiredRef(static::accessor(), $key);
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
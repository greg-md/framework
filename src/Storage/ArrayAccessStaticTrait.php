<?php

namespace Greg\Storage;

use Greg\Tool\Arr;

trait ArrayAccessStaticTrait
{
    static public function has($key, ...$keys)
    {
        return Arr::hasRef(static::$storage, $key, ...$keys);
    }

    static public function hasIndex($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex(static::$storage, $index, $delimiter);
    }

    static public function set($key, $value)
    {
        return Arr::set(static::$storage, $key, $value);
    }

    static public function setRef($key, &$value)
    {
        return Arr::set(static::$storage, $key, $value);
    }

    static public function setIndex($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex(static::$storage, $index, $value, $delimiter);
    }

    static public function setIndexRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::setIndex(static::$storage, $index, $value, $delimiter);
    }

    static public function get($key, $else = null)
    {
        return Arr::get(static::$storage, $key, $else);
    }

    static public function &getRef($key, $else = null)
    {
        return Arr::getRef(static::$storage, $key, $else);
    }

    static public function getForce($key, $else = null)
    {
        return Arr::getForce(static::$storage, $key, $else);
    }

    static public function &getForceRef($key, $else = null)
    {
        return Arr::getForceRef(static::$storage, $key, $else);
    }

    static public function getArray($key, $else = null)
    {
        return Arr::getArray(static::$storage, $key, $else);
    }

    static public function &getArrayRef($key, $else = null)
    {
        return Arr::getArrayRef(static::$storage, $key, $else);
    }

    static public function getArrayForce($key, $else = null)
    {
        return Arr::getArrayForce(static::$storage, $key, $else);
    }

    static public function &getArrayForceRef($key, $else = null)
    {
        return Arr::getArrayForceRef(static::$storage, $key, $else);
    }

    static public function getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex(static::$storage, $index, $else, $delimiter);
    }

    static public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef(static::$storage, $index, $else, $delimiter);
    }

    static public function getIndexForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce(static::$storage, $index, $else, $delimiter);
    }

    static public function &getIndexForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef(static::$storage, $index, $else, $delimiter);
    }

    static public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray(static::$storage, $index, $else, $delimiter);
    }

    static public function &getIndexArrayRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayRef(static::$storage, $index, $else, $delimiter);
    }

    static public function getIndexArrayForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForce(static::$storage, $index, $else, $delimiter);
    }

    static public function &getIndexArrayForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArrayForceRef(static::$storage, $index, $else, $delimiter);
    }

    static public function required($key)
    {
        return Arr::required(static::$storage, $key);
    }

    static public function &requiredRef($key)
    {
        return Arr::requiredRef(static::$storage, $key);
    }

    static public function del($key, ...$keys)
    {
        return Arr::del(static::$storage, $key, ...$keys);
    }

    static public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::delIndex(static::$storage, $index, $delimiter);
    }
}
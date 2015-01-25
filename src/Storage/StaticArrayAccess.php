<?php

namespace Greg\Storage;

trait StaticArrayAccess
{
    static protected $storage = [];

    static public function has($index)
    {
        return array_key_exists($index, static::$storage);
    }

    static public function set($index, $value)
    {
        static::$storage[$index] = $value;

        return true;
    }

    static public function &get($index, $else = null)
    {
        if (static::has($index)) return static::$storage[$index]; return $else;
    }

    static public function del($index)
    {
        unset(static::$storage[$index]);

        return true;
    }

    static public function exchange(array $array)
    {
        static::$storage = $array;

        return true;
    }

    static public function merge(array $array)
    {
        static::$storage = array_merge(static::$storage, $array);

        return true;
    }

    static public function mergePrepend(array $array)
    {
        static::$storage = array_merge($array, static::$storage);

        return true;
    }

    static public function replace(array $array)
    {
        static::$storage = array_replace(static::$storage, $array);

        return true;
    }

    static public function replacePrepend(array $array)
    {
        static::$storage = array_replace($array, static::$storage);

        return true;
    }
}
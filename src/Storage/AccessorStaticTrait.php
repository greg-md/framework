<?php

namespace Greg\Storage;

trait AccessorStaticTrait
{
    static private $storage = [];

    static protected function getStorage()
    {
        return static::$storage;
    }

    static protected function setStorage(array $storage)
    {
        static::$storage = $storage;
    }

    static protected function addToStorage($key, $value)
    {
        static::$storage[$key] = $value;
    }

    static protected function getFromStorage($key)
    {
        return array_key_exists($key, static::$storage) ? static::$storage[$key] : null;
    }

    static protected function mergeStorage(array $storage, $prepend = false)
    {
        static::$storage = $prepend ? array_merge($storage, static::$storage) : array_merge(static::$storage, $storage);
    }

    static protected function replaceStorage(array $storage, $prepend = false)
    {
        static::$storage = $prepend ? array_replace($storage, static::$storage) : array_replace(static::$storage, $storage);
    }
}
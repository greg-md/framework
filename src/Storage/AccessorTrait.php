<?php

namespace Greg\Storage;

trait AccessorTrait
{
    protected $storage = [];

    protected function getStorage()
    {
        return $this->storage;
    }

    protected function setStorage(array $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    protected function addToStorage($key, $value)
    {
        $this->storage[$key] = $value;

        return $this;
    }

    protected function getStorageValue($key)
    {
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
    }

    protected function mergeStorage(array $storage, $prepend = false)
    {
        $this->storage = $prepend ? array_merge($storage, $this->storage) : array_merge($this->storage, $storage);

        return $this;
    }

    protected function replaceStorage(array $storage, $prepend = false)
    {
        $this->storage = $prepend ? array_replace($storage, $this->storage) : array_replace($this->storage, $storage);

        return $this;
    }
}
<?php

namespace Greg\Storage;

trait AccessorTrait
{
    private $storage = [];

    protected function getStorage()
    {
        return $this->storage;
    }

    protected function setStorage(array $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    protected function setStorageRef(array &$storage)
    {
        $this->storage = &$storage;

        return $this;
    }

    protected function addToStorage($key, $value)
    {
        $this->storage[$key] = $value;

        return $this;
    }

    protected function getFromStorage($key)
    {
        return array_key_exists($key, $this->storage) ? $this->storage[$key] : null;
    }
}
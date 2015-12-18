<?php

namespace Greg\Storage;

class Iterator implements \Iterator
{
    use AccessorTrait;

    public function __construct(&$array = [])
    {
        $this->storage = &$array;
    }

    public function current()
    {
        return current($this->storage);
    }

    public function key()
    {
        return key($this->storage);
    }

    public function next()
    {
        return next($this->storage);
    }

    public function rewind()
    {
        return reset($this->storage);
    }

    public function valid()
    {
        return $this->key() !== null;
    }
}
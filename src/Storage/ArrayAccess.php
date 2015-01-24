<?php

namespace Greg\Storage;

trait ArrayAccess
{
    protected $storage = [];

    public function has($index)
    {
        return array_key_exists($index, $this->storage);
    }

    public function set($index, $value)
    {
        $this->storage[$index] = $value;

        return $this;
    }

    public function &get($index, $else = null)
    {
        if ($this->has($index)) return $this->storage[$index]; return $else;
    }

    public function del($index)
    {
        unset($this->storage[$index]);

        return $this;
    }

    public function exchange(array $array)
    {
        $this->storage = $array;

        return $this;
    }

    public function merge(array $array)
    {
        $this->storage = array_merge($this->storage, $array);

        return $this;
    }

    public function mergePrepend(array $array)
    {
        $this->storage = array_merge($array, $this->storage);

        return $this;
    }

    public function replace(array $array)
    {
        $this->storage = array_replace($this->storage, $array);

        return $this;
    }

    public function replacePrepend(array $array)
    {
        $this->storage = array_replace($array, $this->storage);

        return $this;
    }

    /* Magic methods for ArrayAccess interface */

    public function offsetExists($index)
    {
        return call_user_func_array([$this, 'has'], func_get_args());
    }

    public function offsetSet($index, $value)
    {
        return call_user_func_array([$this, 'set'], func_get_args());
    }

    public function &offsetGet($index)
    {
        return $this->storage[$index];
    }

    public function offsetUnset($index)
    {
        return call_user_func_array([$this, 'del'], func_get_args());
    }
}
<?php

namespace Greg\Storage;

use Greg\Support\Arr;

trait ArrayAccess
{
    protected $storage = [];

    public function has($index)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                if (!array_key_exists($index, $this->storage)) {
                    return false;
                }
            }
            return true;
        }
        return array_key_exists($index, $this->storage);
    }

    public function set($index, $value)
    {
        $this->storage[$index] = $value;

        return $this;
    }

    public function &get($index, $else = null)
    {
        if (is_array($index)) {
            $return = array();
            $else = Arr::bring($else);
            foreach(($indexes = $index) as $index) {
                if ($this->has($index)) {
                    $return[$index] = $this->storage[$index];
                } elseif (array_key_exists($index, $else)) {
                    $return[$index] = $else[$index];
                } else {
                    $return[$index] = null;
                }
            }
            return $return;
        }
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

    public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas($this->storage, $index, $delimiter);
    }

    public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::indexSet($this->storage, $index, $value, $delimiter);

        return $this;
    }

    public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet($this->storage, $index, $else, $delimiter);
    }

    public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexDel($this->storage, $index, $delimiter);
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
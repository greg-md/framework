<?php

namespace Greg\Support\Storage;

use Greg\Support\Arr;

trait ArrayAccessTrait
{
    abstract protected function &accessor(array $storage = []);

    public function has($key, ...$keys)
    {
        return Arr::has($this->accessor(), $key, ...$keys);
    }

    public function hasIndex($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex($this->accessor(), $index, $delimiter);
    }

    public function set($key, $value)
    {
        Arr::set($this->accessor(), $key, $value);

        return $this;
    }

    public function setRef($key, &$value)
    {
        Arr::setRef($this->accessor(), $key, $value);

        return $this;
    }

    public function setIndex($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::setIndex($this->accessor(), $index, $value, $delimiter);

        return $this;
    }

    public function setIndexRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::setIndexRef($this->accessor(), $index, $value, $delimiter);

        return $this;
    }

    public function &get($key, $else = null)
    {
        return Arr::get($this->accessor(), $key, $else);
    }

    public function &getRef($key, $else = null)
    {
        return Arr::getRef($this->accessor(), $key, $else);
    }

    public function getArray($key, $else = null)
    {
        return Arr::getArray($this->accessor(), $key, $else);
    }

    public function &getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($this->accessor(), $index, $else, $delimiter);
    }

    public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($this->accessor(), $index, $else, $delimiter);
    }

    public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($this->accessor(), $index, $else, $delimiter);
    }

    public function del($key, ...$keys)
    {
        Arr::del($this->accessor(), $key, ...$keys);

        return $this;
    }

    public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::delIndex($this->accessor(), $index, $delimiter);

        return $this;
    }

    /* Magic methods for ArrayAccess interface */

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetSet($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Leave this alone! It should return direct reference of accessor to be able to add recursive values.
     * It may return a warning of undefined key.
     * Bug: It will create new empty key in array if it does not exists.
     * Fix: I think this will not be a problem. You can use "has" method instead of getting by undefined keys.
     *
     * @param $key
     * @return array|null
     */
    public function &offsetGet($key)
    {
        return $this->accessor()[$key];
        //return $this->getRef($key);
    }

    public function offsetUnset($key)
    {
        return $this->del($key);
    }
}
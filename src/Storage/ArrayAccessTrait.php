<?php

namespace Greg\Storage;

use Greg\Tool\Arr;

trait ArrayAccessTrait
{
    public function has($key, ...$keys)
    {
        return Arr::hasRef($this->storage, $key, ...$keys);
    }

    public function hasIndex($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::hasIndex($this->storage, $index, $delimiter);
    }

    public function set($key, $value)
    {
        Arr::set($this->storage, $key, $value);

        return $this;
    }

    public function setRef($key, &$value)
    {
        Arr::setRef($this->storage, $key, $value);

        return $this;
    }

    public function setIndex($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::setIndex($this->storage, $index, $value, $delimiter);

        return $this;
    }

    public function setIndexRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::setIndexRef($this->storage, $index, $value, $delimiter);

        return $this;
    }

    public function get($key, $else = null)
    {
        return Arr::get($this->storage, $key, $else);
    }

    public function &getRef($key, $else = null)
    {
        return Arr::getRef($this->storage, $key, $else);
    }

    public function getForce($key, $else = null)
    {
        return Arr::getForce($this->storage, $key, $else);
    }

    public function &getForceRef($key, $else = null)
    {
        return Arr::getForceRef($this->storage, $key, $else);
    }

    public function getArray($key, $else = null)
    {
        return Arr::getArray($this->storage, $key, $else);
    }

    public function &getArrayRef($key, $else = null)
    {
        return Arr::getArrayRef($this->storage, $key, $else);
    }

    public function getArrayForce($key, $else = null)
    {
        return Arr::getArrayForce($this->storage, $key, $else);
    }

    public function &getArrayForceRef($key, $else = null)
    {
        return Arr::getArrayForceRef($this->storage, $key, $else);
    }

    public function getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndex($this->storage, $index, $else, $delimiter);
    }

    public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexRef($this->storage, $index, $else, $delimiter);
    }

    public function getIndexForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForce($this->storage, $index, $else, $delimiter);
    }

    public function &getIndexForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexForceRef($this->storage, $index, $else, $delimiter);
    }

    public function getIndexArray($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::getIndexArray($this->storage, $index, $else, $delimiter);
    }

    public function &getIndexArrayRef($index, $else = null)
    {
        return Arr::getIndexArrayRef($this->storage, $index, $else);
    }

    public function getIndexArrayForce($index, $else = null)
    {
        return Arr::getIndexArrayForce($this->storage, $index, $else);
    }

    public function &getIndexArrayForceRef($index, $else = null)
    {
        return Arr::getIndexArrayForceRef($this->storage, $index, $else);
    }

    public function required($key)
    {
        return Arr::required($this->storage, $key);
    }

    public function &requiredRef($key)
    {
        return Arr::requiredRef($this->storage, $key);
    }

    public function del($key, ...$keys)
    {
        Arr::del($this->storage, $key, ...$keys);

        return $this;
    }

    public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::delIndex($this->storage, $index, $delimiter);

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
        return $this->storage[$key];
        //return $this->getRef($key);
    }

    public function offsetUnset($key)
    {
        return $this->del($key);
    }
}
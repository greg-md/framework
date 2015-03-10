<?php

namespace Greg\Storage;

use Greg\Support\Arr;

trait ArrayAccess
{
    abstract protected function &accessor(array $accessor = []);

    public function has($index)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                if (!array_key_exists($index, $this->accessor())) {
                    return false;
                }
            }

            return true;
        }

        if (($index instanceof \Closure)) {
            foreach($this->accessor() as $key => $value) {
                if ($index($value, $key) === true) {
                    return true;
                }
            }

            return false;
        }

        return array_key_exists($index, $this->accessor());
    }

    public function set($index, $value)
    {
        if ($value instanceof ArrayReference) {
            $value = &$value->get();
        }

        return $this->setRef($index, $value);
    }

    public function setRef($index, &$value)
    {
        if ($index !== null) {
            $this->accessor()[$index] = &$value;
        } else {
            $this->accessor()[] = &$value;
        }

        return $this;
    }

    public function &get($index, $else = null)
    {
        if (is_array($index)) {
            $return = [];

            $else = Arr::bring($else);

            foreach(($indexes = $index) as $index) {
                if ($this->has($index)) {
                    $return[$index] = $this->accessor()[$index];
                } elseif (array_key_exists($index, $else)) {
                    $return[$index] = $else[$index];
                } else {
                    $return[$index] = null;
                }
            }

            return $return;
        }

        if ($this->has($index)) return $this->accessor()[$index]; return $else;
    }

    public function del($index)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                unset($this->accessor()[$index]);
            }
        } else {
            unset($this->accessor()[$index]);
        }

        return $this;
    }

    /* May be split index methods in another trait in the future */

    public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            return Arr::indexHas($this->accessor(), $index, $delimiter);
        }

        return $this->has($index);
    }

    public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        if ($value instanceof ArrayReference) {
            $value = &$value->get();
        }

        if (strpos($index, $delimiter) !== false) {
            Arr::indexSet($this->accessor(), $index, $value, $delimiter);
        } else {
            $this->set($index, $value);
        }

        return $this;
    }

    public function indexSetRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            Arr::indexSetRef($this->accessor(), $index, $value, $delimiter);
        } else {
            $this->setRef($index, $value);
        }

        return $this;
    }

    public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            return Arr::indexGet($this->accessor(), $index, $else, $delimiter);
        }

        return $this->get($index, $else);
    }

    public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            return Arr::indexDel($this->accessor(), $index, $delimiter);
        }

        return $this->del($index);
    }

    /* Magic methods for ArrayAccess interface */

    public function offsetExists($index)
    {
        return $this->has($index);
    }

    public function offsetSet($index, $value)
    {
        return $this->set($index, $value);
    }

    public function &offsetGet($index)
    {
        return $this->accessor()[$index];
    }

    public function offsetUnset($index)
    {
        return $this->del($index);
    }
}
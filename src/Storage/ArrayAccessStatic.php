<?php

namespace Greg\Storage;

use Greg\Support\Arr;

trait ArrayAccessStatic
{
    abstract protected function &accessor(array $accessor = []);

    static public function has($index)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                if (!array_key_exists($index, static::accessor())) {
                    return false;
                }
            }

            return true;
        }

        return array_key_exists($index, static::accessor());
    }

    static public function set($index, $value)
    {
        if ($value instanceof ArrayReference) {
            $value = &$value->get();
        }

        return static::setRef($index, $value);
    }

    static public function setRef($index, &$value)
    {
        if ($index !== null) {
            static::accessor()[$index] = &$value;
        } else {
            static::accessor()[] = &$value;
        }

        return true;
    }

    static public function &get($index, $else = null)
    {
        if (is_array($index)) {
            $return = [];

            $else = Arr::bring($else);

            foreach(($indexes = $index) as $index) {
                if (static::has($index)) {
                    $return[$index] = static::accessor()[$index];
                } elseif (array_key_exists($index, $else)) {
                    $return[$index] = $else[$index];
                } else {
                    $return[$index] = null;
                }
            }

            return $return;
        }

        if (static::has($index)) return static::accessor()[$index]; return $else;
    }

    static public function del($index)
    {
        unset(static::accessor()[$index]);

        return true;
    }

    /* May be split index methods in another trait in the future */

    static public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            return Arr::indexHas(static::accessor(), $index, $delimiter);
        }

        return static::has($index);
    }

    static public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        if ($value instanceof ArrayReference) {
            $value = &$value->get();
        }

        if (strpos($index, $delimiter) !== false) {
            Arr::indexSet(static::accessor(), $index, $value, $delimiter);
        } else {
            static::set($index, $value);
        }

        return true;
    }

    static public function indexSetRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            Arr::indexSetRef(static::accessor(), $index, $value, $delimiter);
        } else {
            static::setRef($index, $value);
        }

        return true;
    }

    static public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            return Arr::indexGet(static::accessor(), $index, $else, $delimiter);
        }

        return static::get($index, $else);
    }

    static public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        if (strpos($index, $delimiter) !== false) {
            return Arr::indexDel(static::accessor(), $index, $delimiter);
        }

        return static::del($index);
    }
}
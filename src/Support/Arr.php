<?php

namespace Greg\Support;

use Greg\Support\Storage\ArrayReference;

class Arr
{
    const INDEX_DELIMITER = '.';

    static public function &all(array &$array)
    {
        return $array;
    }

    static public function has(array &$array, $key, ...$keys)
    {
        $keys ? array_unshift($keys, $key) : ($keys = $key);

        return static::hasKey($array, $keys);
    }

    static public function hasKey(array &$array, $key)
    {
        if (is_array($key)) {
            foreach(($keys = $key) as $key) {
                if (!array_key_exists($key, $array)) {
                    return false;
                }
            }

            return true;
        }

        if (($key instanceof \Closure)) {
            foreach($array as $k => &$value) {
                if ($key($value, $k) === true) {
                    return true;
                }
            }
            unset($value);

            return false;
        }

        return array_key_exists($key, $array);
    }

    static public function hasIndex(array &$array, $index, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $key => $index) {
                if (is_array($index)) {
                    if (!(array_key_exists($index, $array) and is_array($array[$key]) and static::hasIndex($array[$key], $index, $delimiter))) {
                        return false;
                    }
                } else {
                    if (!static::hasIndex($array, $index, $delimiter)) {
                        return false;
                    }
                }
            }

            return true;
        }

        if (strpos($index, $delimiter) === false) {
            return static::has($array, $index);
        }

        $myRef = &$array;

        foreach(explode($delimiter, $index) as $index) {
            if (!(is_array($myRef) and array_key_exists($index, $myRef))) {
                return false;
            }

            $myRef = &$myRef[$index];
        }

        return true;
    }

    static public function set(array &$array, $key, $value)
    {
        return static::setRef($array, $key, $value);
    }

    static public function setRef(array &$array, $key, &$value, $return = true)
    {
        Obj::fetchRef($value);

        if ($key !== null) {
            $array[$key] = &$value;
        } else {
            $array[] = &$value;
        }

        return $return;
    }

    static public function setIndex(array &$array, $index, $value, $delimiter = self::INDEX_DELIMITER)
    {
        return static::setIndexRef($array, $index, $value, $delimiter);
    }

    static public function setIndexRef(array &$array, $index, &$value, $delimiter = self::INDEX_DELIMITER)
    {
        Obj::fetchRef($value);

        if (strpos($index, $delimiter) === false) {
            return static::setRef($array, $index, $value);
        }

        $myRef = &$array;

        $indexes = explode($delimiter, $index);

        $lastIndex = array_pop($indexes);

        foreach($indexes as $index) {
            Arr::bringRef($myRef);

            $myRef = &$myRef[$index];
        }

        if ($lastIndex === '') {
            $myRef[] = &$value;
        } else {
            $myRef[$lastIndex] = &$value;
        }

        return true;
    }

    /**
     * @param array $array
     * @param $key
     * @param null $else
     * @return mixed
     */
    static public function get(array &$array, $key, $else = null)
    {
        if (is_array($key)) {
            $return = [];

            static::bringRef($else);

            foreach(($keys = $key) as $k => $key) {
                $return[$k] = static::get($array, $key, array_key_exists($key, $else) ? $else[$key] : null);
            }

            return $return;
        }

        if (static::has($array, $key)) return $array[$key]; return $else;
    }

    static public function &getRef(array &$array, $key, $else = null)
    {
        if (is_array($key)) {
            $return = [];

            static::bringRef($else);

            foreach(($keys = $key) as $k => $key) {
                $return[$k] = &static::getRef($array, $key, array_key_exists($key, $else) ? $else[$key] : null);
            }

            return $return;
        }

        if (static::has($array, $key)) return $array[$key]; return $else;
    }

    static public function getForce(array &$array, $key, $else = null)
    {
        if (is_array($key)) {
            $return = [];

            static::bringRef($else);

            foreach(($keys = $key) as $k => $key) {
                $return[$k] = static::getForce($array, $key, array_key_exists($key, $else) ? $else[$key] : null);
            }

            return $return;
        }

        if (!static::has($array, $key)) {
            $array[$key] = $else;
        }

        return $array[$key];
    }

    static public function &getForceRef(array &$array, $key, $else = null)
    {
        if (is_array($key)) {
            $return = [];

            static::bringRef($else);

            foreach(($keys = $key) as $k => $key) {
                $return[$k] = &static::getForceRef($array, $key, array_key_exists($key, $else) ? $else[$key] : null);
            }

            return $return;
        }

        if (static::has($array, $key)) {
            $array[$key] = $else;
        }

        return $array[$key];
    }

    static public function getArray(array &$array, $key, $else = null)
    {
        return static::bring(static::get($array, $key, $else));
    }

    static public function &getArrayRef(array &$array, $key, $else = null)
    {
        return static::bringRef(static::getRef($array, $key, $else));
    }

    static public function getArrayForce(array &$array, $key, $else = null)
    {
        return static::bring(static::getForce($array, $key, $else));
    }

    static public function &getArrayForceRef(array &$array, $key, $else = null)
    {
        return static::bringRef(static::getForceRef($array, $key, $else));
    }

    static public function getIndex(array &$array, $index, $else = null, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            $return = [];

            static::bringRef($else);

            foreach(($indexes = $index) as $k => $index) {
                $return[$k] = static::getIndex($array, $index, array_key_exists($index, $else) ? $else[$index] : null, $delimiter);
            }

            return $return;
        }

        if (strpos($index, $delimiter) === false) {
            return static::get($array, $index, $else);
        }

        $myRef = &$array;

        foreach(explode($delimiter, $index) as $index) {
            if (!(is_array($myRef) and array_key_exists($index, $myRef))) {
                return $else;
            }

            $myRef = &$myRef[$index];
        }

        return $myRef;
    }

    static public function &getIndexRef(array &$array, $index, $else = null, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            $return = [];

            static::bringRef($else);

            foreach(($indexes = $index) as $k => $index) {
                $return[$k] = &static::getIndexRef($array, $index, array_key_exists($index, $else) ? $else[$index] : null, $delimiter);
            }

            return $return;
        }

        if (strpos($index, $delimiter) === false) {
            return static::getRef($array, $index, $else);
        }

        $myRef = &$array;

        foreach(explode($delimiter, $index) as $index) {
            if (!(is_array($myRef) and array_key_exists($index, $myRef))) {
                return $else;
            }

            $myRef = &$myRef[$index];
        }

        return $myRef;
    }

    static public function getIndexForce(array &$array, $index, $else = null, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            $return = [];

            static::bringRef($else);

            foreach(($indexes = $index) as $k => $index) {
                $return[$k] = static::getIndexForce($array, $index, array_key_exists($index, $else) ? $else[$index] : null, $delimiter);
            }

            return $return;
        }

        if (strpos($index, $delimiter) === false) {
            return static::getForce($array, $index, $else);
        }

        $myRef = &$array;

        $has = true;

        foreach(explode($delimiter, $index) as $index) {
            if (!(is_array($myRef) and array_key_exists($index, $myRef))) {
                $has = false;
            }

            $myRef = &$myRef[$index];
        }

        if (!$has) {
            $myRef = $else;
        }

        return $myRef;
    }

    static public function &getIndexForceRef(array &$array, $index, $else = null, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            $return = [];

            static::bringRef($else);

            foreach(($indexes = $index) as $k => $index) {
                $return[$k] = &static::getIndexForceRef($array, $index, array_key_exists($index, $else) ? $else[$index] : null, $delimiter);
            }

            return $return;
        }

        if (strpos($index, $delimiter) === false) {
            return static::getForceRef($array, $index, $else);
        }

        $myRef = &$array;

        $has = true;

        foreach(explode($delimiter, $index) as $index) {
            if (!(is_array($myRef) and array_key_exists($index, $myRef))) {
                $has = false;
            }

            $myRef = &$myRef[$index];
        }

        if (!$has) {
            $myRef = $else;
        }

        return $myRef;
    }

    static public function getIndexArray(array &$array, $index, $else = null)
    {
        return static::bring(static::getIndex($array, $index, $else));
    }

    static public function &getIndexArrayRef(array &$array, $index, $else = null)
    {
        return static::bringRef(static::getIndexRef($array, $index, $else));
    }

    static public function getIndexArrayForce(array &$array, $index, $else = null)
    {
        return static::bring(static::getIndexForce($array, $index, $else));
    }

    static public function &getIndexArrayForceRef(array &$array, $index, $else = null)
    {
        return static::bringRef(static::getIndexForceRef($array, $index, $else));
    }

    static public function required(array &$array, $key)
    {
        $value = static::get($array, $key);

        if (!$value) {
            throw new \Exception('Undefined value for `' . $key . '`.');
        }

        return $value;
    }

    static public function &requiredRef(array &$array, $key)
    {
        $value = static::getRef($array, $key);

        if (!$value) {
            throw new \Exception('Undefined value for `' . $key . '`.');
        }

        return $value;
    }

    static public function del(array &$array, $key, ...$keys)
    {
        $keys ? array_unshift($keys, $key) : ($keys = $key);

        return static::delKey($array, $keys);
    }

    static public function delKey(array &$array, $key)
    {
        if (is_array($key)) {
            foreach(($keys = $key) as $key) {
                static::del($array, $key);
            }

            return true;
        }

        unset($array[$key]);

        return true;
    }

    static public function delIndex(array &$array, $index, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                static::delIndex($array, $index, $delimiter);
            }

            return true;
        }

        if (strpos($index, $delimiter) === false) {
            return static::del($array, $index);
        }

        $indexes = explode($delimiter, $index);

        $lastIndex = array_pop($indexes);

        $myRef = &$array;

        foreach($indexes as $index) {
            if (!is_array($myRef)) {
                break;
            }

            $myRef = &$myRef[$index];
        }

        if (is_array($myRef)) {
            $myRef[$lastIndex] = null;

            unset($myRef[$lastIndex]);
        }

        return true;
    }

    static public function append(array &$array, $value = null, ...$values)
    {
        $array[] = $value;

        if ($values) {
            $array = array_merge($array, $values);
        }

        return true;
    }

    static public function appendRef(array &$array, $value = null, ...$values)
    {
        $array[] = &$value;

        if ($values) {
            foreach($values as &$val) {
                $array[] = &$val;
            }
            unset($val);
        }

        return true;
    }

    static public function appendKey(array &$array, $key = null, $value = null)
    {
        return static::appendKeyRef($array, $key, $value);
    }

    static public function appendKeyRef(array &$array, $key = null, &$value = null)
    {
        if ($key === null) {
            $array[] = &$value;
        } else {
            unset($array[$key]);

            $array[$key] = &$value;
        }

        return true;
    }

    static public function appendIndex(array &$array, $index = null, $value = null, $delimiter = self::INDEX_DELIMITER)
    {
        return static::appendIndexRef($array, $index, $value, $delimiter);
    }

    static public function appendIndexRef(array &$array, $index = null, &$value = null, $delimiter = self::INDEX_DELIMITER)
    {
        $indexes = explode($delimiter, $index);

        $lastIndex = array_pop($indexes);

        if ($lastIndex === '') {
            $lastIndex = null;
        }

        return static::appendKeyRef(static::getIndexArrayForceRef($array, implode($delimiter, $indexes)), $lastIndex, $value);
    }

    static public function prepend(array &$array, $value = null, ...$values)
    {
        array_unshift($array, $value, ...$values);

        return true;
    }

    static public function prependRef(array &$array, &$value = null, &...$values)
    {
        static::prependKeyRef($array, null, $value);

        if ($values) {
            foreach($values as &$value) {
                static::prependKeyRef($array, null, $value);
            }
            unset($val);
        }

        return true;
    }

    static public function prependKey(array &$array, $key = null, $value = null)
    {
        return static::prependKeyRef($array, $key, $value);
    }

    static public function prependKeyRef(array &$array, $key = null, &$value = null)
    {
        if ($key === null) {
            array_unshift($array, null);

            $array[0] = &$value;
        } else {
            $array = [$key => &$value] + $array;
        }

        return true;
    }

    static public function prependIndex(array &$array, $index = null, $value = null, $delimiter = self::INDEX_DELIMITER)
    {
        return static::prependIndexRef($array, $index, $value, $delimiter);
    }

    static public function prependIndexRef(array &$array, $index = null, &$value = null, $delimiter = self::INDEX_DELIMITER)
    {
        $indexes = explode($delimiter, $index);

        $lastIndex = array_pop($indexes);

        if ($lastIndex === '') {
            $lastIndex = null;
        }

        return static::prependKeyRef(static::getIndexArrayForceRef($array, implode($delimiter, $indexes)), $lastIndex, $value);
    }

    static public function &first(array &$array, callable $callable = null, $else = null)
    {
        if ($callable !== null) {
            foreach ($array as $key => &$value) {
                if (call_user_func_array($callable, [$value, $key])) return $value;
            }
            unset($value);

            return $else;
        }

        if ($array) {
            reset($array);

            return $array[key($array)];
        }

        return $else;
    }

    static public function &last(array &$array, callable $callable = null, $else = null)
    {
        if ($callable !== null) {
            return static::first($reverse = array_reverse($array), $callable, $else);
        }

        if ($array) {
            end($array);

            return $array[key($array)];
        }

        return $else;
    }

    /**
     * Bring the variable to an array.
     *
     * @param $var
     * @param ...$vars
     * @return array
     */
    static public function bring($var, ...$vars)
    {
        return static::bringRef($var, ...$vars);
    }

    static public function &bringRef(&$var, &...$vars)
    {
        $var = static::bringVar($var);

        if (!$vars) {
            return $var;
        }

        foreach($vars as &$v) {
            static::bringVar($v);
        }
        unset($v);

        static::prependRef($vars, $var);

        return $vars;
    }

    static public function &bringVar(&$var)
    {
        if (is_array($var)) {
            return $var;
        }

        if (is_scalar($var) or is_null($var)) {
            $var = (array)$var;

            return $var;
        }

        $var = [$var];

        return $var;
    }

    static public function mapRecursive(callable $callable, array &$array, array &...$arrays)
    {
        $k = 0;

        foreach($array as &$value) {
            $callArgs = [];

            foreach($arrays as &$arr) {
                $callArgs[] = &$arr[$k];
            }
            unset($arr);

            if (is_array($value)) {
                static::mapRecursive($callable, $value, ...$callArgs);
            } else {
                static::prependRef($callArgs, $value);

                $value = call_user_func_array($callable, [$callArgs]);
            }

            ++$k;
        }
        unset($value);

        return $array;
    }

    static public function &find(array &$array, callable $callable, $else = null)
    {
        return static::first($array, $callable, $else);
    }

    /**
     * @param array $array
     * @param callable ...$callable
     * @param int ...$flag
     * @return bool
     */
    static public function filterRecursive(array $array, ...$args)
    {
        return static::filterRecursiveMe($array, ...$args);
    }

    static public function filterRecursiveMe(array &$array, ...$args)
    {
        foreach($array as &$value) {
            if (is_array($value)) {
                static::filterRecursive($value, ...$args);
            }
        }
        unset($value);

        $array = array_filter($array, ...$args); return $array;
    }

    static public function group(array $arrays, $maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        return static::groupMe($arrays, $maxLevel, $replaceLast, $removeGroupedKey);
    }

    static public function groupMe(array &$arrays, $maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        $grouped = [];

        foreach($arrays as &$array) {
            if (($maxLevel instanceof \Closure)) {
                if ($replaceLast) {
                    $grouped[$maxLevel($array)] = $array;
                } else {
                    $grouped[$maxLevel($array)][] = $array;
                }
            } else {
                $current = &$grouped;

                if (is_numeric($maxLevel)) {
                    $i = 1;

                    foreach($array as $key => &$value) {
                        if ($i > $maxLevel) {
                            break;
                        }

                        $current = &$current[$value];

                        if ($removeGroupedKey) {
                            unset($array[$key]);
                        }

                        ++$i;
                    }
                    unset($value);
                } else {
                    Arr::bringRef($maxLevel);

                    foreach((array)$maxLevel as $level) {
                        $current = &$current[$array[$level]];

                        if ($removeGroupedKey) {
                            unset($array[$level]);
                        }
                    }
                }

                if ($replaceLast) {
                    $current = $array;
                } else {
                    $current[] = $array;
                }
            }
        }
        unset($array);

        $arrays = $grouped; return $arrays;
    }

    static public function filled(array &$array, ...$args)
    {
        return sizeof(array_filter($array, ...$args)) == sizeof($array);
    }

    static public function ref(&$var)
    {
        return new ArrayReference($var);
    }

    static public function each(array $array, callable $callable)
    {
        $new = [];

        foreach($array as $key => $value) {
            list($newValue, $newKey) = $callable($value, $key);

            if ($newKey === null) {
                $new[] = $newValue;
            } else {
                $new[$newKey] = $newValue;
            }
        }

        return $new;
    }

    static public function pack(array $array, $glue = null, $saveKeys = false)
    {
        $new = [];

        foreach($array as $key => $value) {
            if ($saveKeys) {
                $new[$key] = $key . $glue . $value;
            } else {
                $new[] = $key . $glue . $value;
            }
        }

        return $new;
    }

    static public function fixIndexes(array $array, $delimiter = self::INDEX_DELIMITER)
    {
        $pack = static::packIndexes($array, $delimiter);

        $unpack = static::unpackIndexes($pack, $delimiter);

        return $unpack;
    }

    static public function packIndexes(array $array, $delimiter = self::INDEX_DELIMITER)
    {
        $new = [];

        foreach($array as $key => $value) {
            if (is_array($value)) {
                $value = static::packIndexes($value);

                foreach($value as $k => $v) {
                    $new[$key . $delimiter . $k] = $v;
                }
            } else {
                $new[$key] = $value;
            }
        }

        return $new;
    }

    static public function unpackIndexes(array $array, $delimiter = self::INDEX_DELIMITER)
    {
        $new = [];

        foreach($array as $key => $value) {
            $sub = &$new;

            foreach(explode($delimiter, $key) as $k) {
                if ($k === null) {
                    $sub = &$sub[];
                } else {
                    if (is_array($sub) and static::has($sub, $k) and !is_array($sub[$k])) {
                        $sub[$k] = [];
                    }
                    $sub = &$sub[$k];
                }
            }

            $sub = $value;
        }

        return $new;
    }
}
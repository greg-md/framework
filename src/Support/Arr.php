<?php

namespace Greg\Support;

use Greg\Storage\ArrayReference;

class Arr
{
    const INDEX_DELIMITER = '.';

    /**
     * Add a value to the beginning of the array.
     *
     * @param array $array
     * @param $value
     * @param null $index
     * @return bool
     */
    static public function prepend(array &$array, $value, $index = null)
    {
        if ($index === null) {
            array_unshift($array, $value);
        } else {
            $array = [$index => $value] + $array;
        }

        return true;
    }

    /**
     * @param array $array
     * @param callable $callable
     * @param null $else
     * @return mixed
     */
    static public function first(array $array, callable $callable = null, $else = null)
    {
        if ($callable !== null) {
            foreach ($array as $key => $value) {
                if (call_user_func($callable, $value, $key)) return $value;
            }
        }

        if ($array) {
            return reset($array);
        }

        return $else;
    }

    /**
     * Bring the variable to an array.
     *
     * @param $var
     * @return array
     */
    static public function bring($var)
    {
        if (is_array($var)) {
            return $var;
        }

        if (is_scalar($var) or is_null($var)) {
            return (array)$var;
        }

        return [$var];
    }

    static public function indexHas($array, $index, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $key => $index) {
                if (is_array($index)) {
                    if (!(array_key_exists($index, $array) and is_array($array[$key]) and static::indexHas($array[$key], $index, $delimiter))) {
                        return false;
                    }
                } else {
                    if (!static::indexHas($array, $index, $delimiter)) {
                        return false;
                    }
                }
            }

            return true;
        }

        return static::currentIndexHas($array, $index, $delimiter);
    }

    static public function currentIndexHas(array $array, $index, $delimiter = self::INDEX_DELIMITER)
    {
        $indexes = explode($delimiter, $index);

        foreach($indexes as $index) {
            if (!(is_array($array) and array_key_exists($index, $array))) {
                return false;
            }

            $array = $array[$index];
        }

        return true;
    }

    static public function indexSet(&$array, $index, $value, $delimiter = self::INDEX_DELIMITER)
    {
        return static::indexSetRef($array, $index, $value, $delimiter);
    }

    static public function indexSetRef(&$array, $index, &$value, $delimiter = self::INDEX_DELIMITER)
    {
        $indexes = explode($delimiter, $index);

        $current = &$array;

        foreach($indexes as $index) {
            $current = (array)$current;

            $current = &$current[$index];
        }

        $current = &$value;

        return true;
    }

    static public function &indexGet(&$array, $index, $else = null, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            $else = (array)$else;

            $return = [];

            foreach(($indexes = $index) as $key => $index) {
                $keyElse = array_key_exists($key, $else) ? $else[$key] : null;

                if (is_array($index)) {
                    $child = array_key_exists($key, $array) ? (array)$array[$key] : [];

                    $return[$key] = static::indexGet($child, $index, $keyElse, $delimiter);
                } else {
                    $return[$key] = static::indexGet($array, $index, $keyElse, $delimiter);
                }
            }

            return $return;
        }

        return static::subValue($array, explode($delimiter, $index), $else);
    }

    static public function &subValue(&$array, $indexes, $else = null)
    {
        if (!$indexes) {
            return $array;
        }

        if (!is_array($array)) {
            return $else;
        }

        $index = array_shift($indexes);

        if (!array_key_exists($index, $array)) {
            return $else;
        }

        return static::subValue($array[$index], $indexes, $else);
    }

    static public function indexDel(&$array, $index, $delimiter = self::INDEX_DELIMITER)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $key => $index) {
                if (is_array($index)) {
                    if (array_key_exists($key, $array) and is_array($array[$key])) {
                        static::indexDel($array[$key], $index, $delimiter);
                    }
                } else {
                    static::indexDel($array, $index, $delimiter);
                }
            }

            return true;
        }

        $indexes = explode($delimiter, $index);

        $lastIndex = array_pop($indexes);

        $current = &$array;

        foreach($indexes as $index) {
            if (!is_array($current)) {
                break;
            }

            $current = &$current[$index];
        }

        if (is_array($current)) {
            $current[$lastIndex] = null;

            unset($current[$lastIndex]);
        }

        return true;
    }

    static public function mapRecursive(callable $callable, array $array, array ...$arrays)
    {
        $k = 0;

        foreach($array as &$value) {
            $callArgs = [];

            foreach($arrays as $arg) {
                $callArgs[] = $arg[$k];
            }

            if (is_array($value)) {
                static::mapRecursive($callable, $value, ...$callArgs);
            } else {
                array_unshift($callArgs, $value);

                $value = call_user_func_array($callable, $callArgs);
            }

            ++$k;
        }

        return $array;
    }

    static public function find(array $array, callable $callable)
    {
        foreach($array as $value) {
            if (call_user_func_array($callable, [$value])) return $value;
        }

        return false;
    }

    /**
     * @param array $array
     * @param callable ...$callable
     * @param int ...$flag
     * @return array
     */
    static public function filterRecursive(array $array, ...$args)
    {
        foreach($array as &$value) {
            if (is_array($value)) {
                $value = static::filterRecursive($value, ...$args);
            }
        }

        return array_filter($array, ...$args);
    }

    static public function group(array $array, $maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        $grouped = [];

        foreach($array as $item) {
            if (($maxLevel instanceof \Closure)) {
                if ($replaceLast) {
                    $grouped[$maxLevel($item)] = $item;
                } else {
                    $grouped[$maxLevel($item)][] = $item;
                }
            } else {
                $current = &$grouped;

                if (is_numeric($maxLevel)) {
                    $i = 1;

                    foreach($item as $key => $value) {
                        if ($i > $maxLevel) {
                            break;
                        }

                        $current = &$current[$value];

                        if ($removeGroupedKey) {
                            unset($item[$key]);
                        }

                        ++$i;
                    }
                } else {
                    foreach((array)$maxLevel as $level) {
                        $current = &$current[$item[$level]];

                        if ($removeGroupedKey) {
                            unset($item[$level]);
                        }
                    }
                }

                if ($replaceLast) {
                    $current = $item;
                } else {
                    $current[] = $item;
                }
            }
        }

        return $grouped;
    }

    static public function filled($array, ...$args)
    {
        return sizeof(array_filter($array, ...$args)) == sizeof($array);
    }

    static public function ref(&$var)
    {
        return new ArrayReference($var);
    }
}
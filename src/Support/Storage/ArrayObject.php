<?php

namespace Greg\Support\Storage;

use Greg\Support\Arr;

class ArrayObject implements \ArrayAccess, \IteratorAggregate, \Serializable, \Countable
{
    use Accessor, ArrayAccess, IteratorAggregate, Serializable, Countable;

    public function __construct($input = [], $iteratorClass = null)
    {
        $this->mergeMe(Arr::bring($input));

        if ($iteratorClass !== null) {
            $this->iteratorClass($iteratorClass);
        }

        return $this;
    }

    protected function &fixArray(&$array)
    {
        return Arr::bringRef($array);
    }

    static public function newArrayObject($input, $iteratorClass = null)
    {
        return new ArrayObject($input, $iteratorClass);
    }

    public function exchange($input)
    {
        return $this->exchangeRef($input);
    }

    public function exchangeRef(&$input)
    {
        $this->storage = &$this->fixArray($input);

        return $this;
    }

    public function toArray()
    {
        return $this->storage;
    }

    public function toArrayObject()
    {
        return $this->newArrayObject($this->storage);
    }

    public function append($value = null, ...$values)
    {
        Arr::append($this->storage, $value, ...$values);

        return $this;
    }

    public function appendRef(&$value = null, &...$values)
    {
        Arr::appendRef($this->storage, $value, ...$values);

        return $this;
    }

    public function appendKey($value = null, ...$values)
    {
        Arr::appendKey($this->storage, $value, ...$values);

        return $this;
    }

    public function appendKeyRef(&$value = null, &...$values)
    {
        Arr::appendKeyRef($this->storage, $value, ...$values);

        return $this;
    }

    public function prepend($value = null, ...$values)
    {
        Arr::prepend($this->storage, $value, ...$values);

        return $this;
    }

    public function prependRef(&$value, &...$values)
    {
        Arr::prependRef($this->storage, $value, ...$values);

        return $this;
    }

    public function prependKey($value = null, $index = null)
    {
        Arr::prependKey($this->storage, $value, $index);

        return $this;
    }

    public function prependKeyRef(&$value = null, $index = null)
    {
        Arr::prependKeyRef($this->storage, $value, $index);

        return $this;
    }

    public function asort($flag = SORT_REGULAR)
    {
        asort($this->storage, $flag);

        return $this;
    }

    public function ksort($flag = SORT_REGULAR)
    {
        ksort($this->storage, $flag);

        return $this;
    }

    public function natcasesort()
    {
        natcasesort($this->storage);

        return $this;
    }

    public function natsort()
    {
        natsort($this->storage);

        return $this;
    }

    public function uasort($function)
    {
        uasort($this->storage, $function);

        return $this;
    }

    public function uksort($function)
    {
        uksort($this->storage, $function);

        return $this;
    }

    public function reset()
    {
        reset($this->storage);

        return $this;
    }

    public function clear()
    {
        $this->storage = [];

        return $this;
    }

    public function inArray($value, $strict = false)
    {
        return in_array($value, $this->storage, $strict);
    }

    public function inArrayValues($values, $strict = false)
    {
        foreach($values as $value) {
            if (!$this->inArray($value, $strict)) {
                return false;
            }
        }

        return true;
    }

    public function merge(array $array, array ...$arrays)
    {
        return $this->newArrayObject(array_merge($this->storage, $array, ...$arrays));
    }

    public function mergeMe(array $array, array ...$arrays)
    {
        $this->storage = array_merge($this->storage, $array, ...$arrays);

        return $this;
    }

    public function mergeRecursive(array $array, array ...$arrays)
    {
        return $this->newArrayObject(array_merge_recursive($this->toArray(), $array, ...$arrays));
    }

    public function mergeRecursiveMe(array $array, array ...$arrays)
    {
        $this->exchange(array_merge_recursive($this->toArray(), $array, ...$arrays));

        return $this;
    }

    public function mergePrepend(array $array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->storage;

        return $this->newArrayObject(array_merge(...$arrays));
    }

    public function mergePrependMe(array $array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->storage;

        $this->storage = array_merge(...$arrays);

        return $this;
    }

    public function mergePrependRecursive(array $array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->toArray();

        return $this->newArrayObject(array_merge_recursive(...$arrays));
    }

    public function mergePrependRecursiveMe(array $array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->toArray();

        $this->exchange(array_merge_recursive(...$arrays));

        return $this;
    }

    public function mergeValues()
    {
        return $this->newArrayObject(array_merge(...$this->storage));
    }

    public function mergeValuesMe()
    {
        $this->storage = array_merge(...$this->storage);

        return $this;
    }

    public function replace($array, array ...$arrays)
    {
        return $this->newArrayObject(array_replace($this->storage, $array, ...$arrays));
    }

    public function replaceMe($array, array ...$arrays)
    {
        $this->storage = array_replace($this->storage, $array, ...$arrays);

        return $this;
    }

    public function replaceRecursive($array, array ...$arrays)
    {
        return $this->newArrayObject(array_replace_recursive($this->toArray(), $array, ...$arrays));
    }

    public function replaceRecursiveMe($array, array ...$arrays)
    {
        $this->exchange(array_replace_recursive($this->toArray(), $array, ...$arrays));

        return $this;
    }

    public function replacePrepend($array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->storage;

        return $this->newArrayObject(array_replace(...$arrays));
    }

    public function replacePrependMe($array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->storage;

        $this->storage = array_replace(...$arrays);

        return $this;
    }

    public function replacePrependRecursive($array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->toArray();

        return $this->newArrayObject(array_replace_recursive(...$arrays));
    }

    public function replacePrependRecursiveMe($array, array ...$arrays)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->toArray();

        $this->exchange(array_replace_recursive(...$arrays));

        return $this;
    }

    public function replaceValues()
    {
        return $this->newArrayObject(array_replace(...$this->storage));
    }

    public function replaceValuesMe()
    {
        $this->storage = array_replace(...$this->storage);

        return $this;
    }

    public function diff($array, array ...$arrays)
    {
        return $this->newArrayObject(array_diff($this->storage, $array, ...func_get_args()));
    }

    public function diffMe($array, array ...$arrays)
    {
        $this->storage = array_diff($this->storage, $array, ...func_get_args());

        return $this;
    }

    public function map(callable $callable = null, array ...$arrays)
    {
        return $this->newArrayObject(array_map($callable, $this->storage, ...$arrays));
    }

    public function mapMe(callable $callable = null, array ...$arrays)
    {
        $this->storage = array_map($callable, $this->storage, ...$arrays);

        return $this;
    }

    public function mapRecursive(callable $callable = null, array ...$arrays)
    {
        return $this->newArrayObject(Arr::mapRecursive($callable, $this->toArray(), ...$arrays));
    }

    public function mapRecursiveMe(callable $callable = null, array ...$arrays)
    {
        $this->exchange(Arr::mapRecursive($callable, $this->toArray(), ...$arrays));

        return $this;
    }

    public function find(callable $callable = null)
    {
        return Arr::find($this->storage, $callable);
    }

    public function filter(callable $callable = null, $flag = 0)
    {
        return $this->newArrayObject(array_filter($this->storage, ...func_get_args()));
    }

    public function filterMe(callable $callable = null, $flag = 0)
    {
        $this->storage = array_filter($this->storage, ...func_get_args());

        return $this;
    }

    public function filterRecursive(callable $callable = null, $flag = 0)
    {
        return $this->newArrayObject(Arr::filterRecursive($this->toArray(), ...func_get_args()));
    }

    public function filterRecursiveMe(callable $callable = null, $flag = 0)
    {
        $this->exchange(Arr::filterRecursive($this->toArray(), ...func_get_args()));

        return $this;
    }

    public function reverse($preserveKeys = false)
    {
        return $this->newArrayObject(array_reverse($this->storage, $preserveKeys));
    }

    public function reverseMe($preserveKeys = false)
    {
        $this->storage = array_reverse($this->storage, $preserveKeys);

        return $this;
    }

    public function chunk($size, $preserveKeys = false)
    {
        return $this->newArrayObject(array_chunk($this->storage, $size, $preserveKeys));
    }

    public function chunkMe($size, $preserveKeys = false)
    {
        $this->exchange(array_chunk($this->storage, $size, $preserveKeys));

        return $this;
    }

    public function implode($param = '')
    {
        return implode($param, $this->storage);
    }

    public function join($param = '')
    {
        return $this->implode($param);
    }

    public function shift()
    {
        return array_shift($this->storage);
    }

    public function pop()
    {
        return array_pop($this->storage);
    }

    public function first()
    {
        reset($this->storage);

        return current($this->storage);
    }

    public function last()
    {
        return end($this->storage);
    }

    public function current()
    {
        return key($this->storage);
    }

    public function next()
    {
        return next($this->storage);
    }

    public function group($maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        return $this->newArrayObject(Arr::group($this->toArray(), $maxLevel, $replaceLast, $removeGroupedKey));
    }

    public function groupMe($maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        $this->exchange(Arr::group($this->toArray(), $maxLevel, $replaceLast, $removeGroupedKey));

        return $this;
    }

    public function column($key, $indexKey = null)
    {
        return $this->newArrayObject(array_column($this->toArray(), ...func_get_args()));
    }

    public function walk(callable $callable, $data = null)
    {
        array_walk($this->storage, $callable, $data);

        return $this;
    }

    public function shuffle()
    {
        shuffle($this->storage);

        return $this;
    }

    public function sort($flags = SORT_REGULAR)
    {
        sort($this->storage, $flags);

        return $this;
    }

    public function arsort($flags = SORT_REGULAR)
    {
        arsort($this->storage, $flags);

        return $this;
    }

    public function krsort($flags = SORT_REGULAR)
    {
        krsort($this->storage, $flags);

        return $this;
    }

    public function keys()
    {
        return $this->newArrayObject(array_keys($this->storage));
    }

    public function values()
    {
        return $this->newArrayObject(array_values($this->storage));
    }
}
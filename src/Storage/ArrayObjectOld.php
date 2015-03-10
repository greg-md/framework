<?php

namespace Greg\Storage;

use Greg\Support\Arr;

class ArrayObjectOld extends \ArrayObject
{
    public function __construct($input = [], $iteratorClass = 'ArrayIterator', $flag = \ArrayObject::ARRAY_AS_PROPS)
    {
        parent::__construct(Arr::bring($input), $flag, $iteratorClass);
    }

    protected function fixArray($array)
    {
        return Arr::bring($array);
    }

    protected function returnArray($input)
    {
        return new ArrayObject($input);
    }

    public function has($index)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                if (!parent::offsetExists((string)$index)) {
                    return false;
                }
            }

            return true;
        }

        if (($index instanceof \Closure)) {
            foreach($this as $key => $value) {
                if ($index($value, $key) === true) {
                    return true;
                }
            }

            return false;
        }

        return parent::offsetExists((string)$index);
    }

    public function set($index, $value)
    {
        parent::offsetSet($index, $value);

        return $this;
    }

    public function &get($index, $else = null)
    {
        if (is_array($index)) {
            $return = [];

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

            return $this->returnArray($return);
        }

        if ($this->has($index)) return parent::offsetGet($index); return $else;
    }

    public function del($index)
    {
        if (is_array($index)) {
            foreach(($indexes = $index) as $index) {
                parent::offsetUnset($index);
            }
        } else {
            parent::offsetUnset($index);
        }

        return $this;
    }

    public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexHas($this, $index, $delimiter);
    }

    public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::indexSet($this, $index, $value, $delimiter);

        return $this;
    }

    public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        return Arr::indexGet($this, $index, $else, $delimiter);
    }

    public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        Arr::indexDel($this, $index, $delimiter);

        return $this;
    }

    public function exchange($input)
    {
        return parent::exchangeArray($this->fixArray($input));
    }

    public function toArray()
    {
        return parent::getArrayCopy();
    }

    public function append($value)
    {
        parent::append($value);

        return $this;
    }

    public function getArrayCopy()
    {
        return $this->toArray();
    }

    public function prepend($value)
    {
        $copy = parent::getArrayCopy();

        array_unshift($copy, $value);

        parent::exchangeArray($copy);

        return $this;
    }

    public function exchangeArray($input)
    {
        return $this->exchange($input);
    }

    public function asort()
    {
        parent::asort();

        return $this;
    }

    public function ksort()
    {
        parent::ksort();

        return $this;
    }

    public function natcasesort()
    {
        parent::natcasesort();

        return $this;
    }

    public function natsort()
    {
        parent::natsort();

        return $this;
    }

    public function offsetExists($index)
    {
        return $this->has($index);
    }

    public function offsetSet($index, $value)
    {
        return $this->set($index, $value);
    }

    public function offsetUnset($index)
    {
        return $this->del($index);
    }

    public function setFlags($flags)
    {
        parent::setFlags($flags);

        return $this;
    }

    public function setIteratorClass($class)
    {
        parent::setIteratorClass($class);

        return $this;
    }

    public function uasort($function)
    {
        parent::uasort($function);

        return $this;
    }

    public function uksort($function)
    {
        parent::uksort($function);

        return $this;
    }

    public function unserialize($serialized)
    {
        parent::unserialize($serialized);

        return $this;
    }

    public function reset()
    {
        reset($this);

        return $this;
    }

    public function clear()
    {
        parent::exchangeArray([]);

        return $this;
    }

    public function inArray($value, $strict = false)
    {
        return in_array($value, parent::getArrayCopy(), $strict);
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

    public function merge($array, $_ = null)
    {
        return $this->returnArray(array_merge(parent::getArrayCopy(), ...func_get_args()));
    }

    public function mergeRecursive($array, $_ = null)
    {
        return $this->returnArray(array_merge_recursive($this->getArrayCopy(), ...func_get_args()));
    }

    public function mergePrepend($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = parent::getArrayCopy();

        return $this->returnArray(array_merge(...$arrays));
    }

    public function mergePrependRecursive($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->getArrayCopy();

        return $this->returnArray(array_merge_recursive(...$arrays));
    }

    public function mergeValues()
    {
        return $this->returnArray(array_merge(...parent::getArrayCopy()));
    }

    public function selfMerge($array, $_ = null)
    {
        parent::exchangeArray(array_merge(parent::getArrayCopy(), ...func_get_args()));

        return $this;
    }

    public function selfMergeRecursive($array, $_ = null)
    {
        $this->exchangeArray(array_merge_recursive($this->getArrayCopy(), ...func_get_args()));

        return $this;
    }

    public function selfMergePrepend($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = parent::getArrayCopy();

        parent::exchangeArray(array_merge(...$arrays));

        return $this;
    }

    public function selfMergePrependRecursive($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->getArrayCopy();

        $this->exchangeArray(array_merge_recursive(...$arrays));

        return $this;
    }

    public function selfMergeValues()
    {
        parent::exchangeArray(array_merge(...parent::getArrayCopy()));

        return $this;
    }

    public function replace($array, $_ = null)
    {
        return $this->returnArray(array_replace(parent::getArrayCopy(), ...func_get_args()));
    }

    public function replaceRecursive($array, $_ = null)
    {
        return $this->returnArray(array_replace_recursive($this->getArrayCopy(), ...func_get_args()));
    }

    public function replacePrepend($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = parent::getArrayCopy();

        return $this->returnArray(array_replace(...$arrays));
    }

    public function replacePrependRecursive($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->getArrayCopy();

        return $this->returnArray(array_replace_recursive(...$arrays));
    }

    public function replaceValues()
    {
        return $this->returnArray(array_replace(...parent::getArrayCopy()));
    }

    public function selfReplace($array, $_ = null)
    {
        parent::exchangeArray(array_replace(parent::getArrayCopy(), ...func_get_args()));

        return $this;
    }

    public function selfReplaceRecursive($array, $_ = null)
    {
        $this->exchangeArray(array_replace_recursive($this->getArrayCopy(), ...func_get_args()));

        return $this;
    }

    public function selfReplacePrepend($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = parent::getArrayCopy();

        parent::exchangeArray(array_replace(...$arrays));

        return $this;
    }

    public function selfReplacePrependRecursive($array, $_ = null)
    {
        $arrays = array_reverse(func_get_args());

        $arrays[] = $this->getArrayCopy();

        $this->exchangeArray(array_replace_recursive(...$arrays));

        return $this;
    }

    public function selfReplaceValues()
    {
        parent::exchangeArray(array_replace(...parent::getArrayCopy()));

        return $this;
    }

    public function diff($array, $_ = null)
    {
        return $this->returnArray(array_diff(parent::getArrayCopy(), ...func_get_args()));
    }

    public function selfDiff($array, $_ = null)
    {
        parent::exchangeArray(array_diff(parent::getArrayCopy(), ...func_get_args()));

        return $this;
    }

    public function map(callable $callable = null, array ...$arrays)
    {
        return $this->returnArray(array_map($callable, parent::getArrayCopy(), ...$arrays));
    }

    public function mapRecursive(callable $callable = null, array ...$arrays)
    {
        return $this->returnArray(Arr::mapRecursive($callable, $this->getArrayCopy(), ...$arrays));
    }

    public function selfMap(callable $callable = null, array ...$arrays)
    {
        parent::exchangeArray(array_map($callable, parent::getArrayCopy(), ...$arrays));

        return $this;
    }

    public function selfMapRecursive(callable $callable = null, array ...$arrays)
    {
        $this->exchangeArray(Arr::mapRecursive($this->getArrayCopy(), func_get_args()));

        return $this;
    }

    public function find(callable $callable = null)
    {
        return Arr::find(parent::getArrayCopy(), $callable);
    }

    public function filter(callable $callable = null, $flag = 0)
    {
        return $this->returnArray(array_filter(parent::getArrayCopy(), ...func_get_args()));
    }

    public function filterRecursive(callable $callable = null, $flag = 0)
    {
        return $this->returnArray(Arr::filterRecursive($this->getArrayCopy(), ...func_get_args()));
    }

    public function selfFilter(callable $callable = null, $flag = 0)
    {
        parent::exchangeArray(array_filter(parent::getArrayCopy(), ...func_get_args()));

        return $this;
    }

    public function selfFilterRecursive(callable $callable = null, $flag = 0)
    {
        $this->exchangeArray(Arr::filterRecursive($this->getArrayCopy(), func_get_args()));

        return $this;
    }

    public function reverse($preserveKeys = false)
    {
        return $this->returnArray(array_reverse(parent::getArrayCopy(), $preserveKeys));
    }

    public function selfReverse($preserveKeys = false)
    {
        parent::exchangeArray(array_reverse(parent::getArrayCopy(), $preserveKeys));

        return $this;
    }

    public function chunk($size, $preserveKeys = false)
    {
        return $this->returnArray(array_chunk(parent::getArrayCopy(), $size, $preserveKeys));
    }

    public function selfChunk($size, $preserveKeys = false)
    {
        parent::exchangeArray(array_chunk(parent::getArrayCopy(), $size, $preserveKeys));

        return $this;
    }

    public function implode($param = '')
    {
        return $this->count() ? implode($param, parent::getArrayCopy()) : '';
    }

    public function join($param = '')
    {
        return $this->implode($param);
    }

    public function shift()
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            $item = array_shift($copy);

            parent::exchangeArray($copy);

            return $item;
        }

        return null;
    }

    public function pop()
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            $item = array_pop($copy);

            parent::exchangeArray($copy);

            return $item;
        }

        return null;
    }

    public function first()
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            reset($copy);

            return current($copy);
        }

        return null;
    }

    public function last()
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            reset($copy);

            return end($copy);
        }

        return null;
    }

    public function current()
    {
        return key($this);
    }

    public function next()
    {
        return next($this);
    }

    public function toArrayObject()
    {
        return $this->returnArray(parent::getArrayCopy());
    }

    public function group($maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        return $this->returnArray(Arr::group($this->getArrayCopy(), $maxLevel, $replaceLast, $removeGroupedKey));
    }

    public function selfGroup($maxLevel = 1, $replaceLast = true, $removeGroupedKey = false)
    {
        parent::exchangeArray(Arr::group($this->getArrayCopy(), $maxLevel, $replaceLast, $removeGroupedKey));

        return $this;
    }

    public function column($key)
    {
        $array = [];

        if ($this->count()) {
            $items = $this->getArrayCopy();

            if (function_exists('array_column')) {
                $array = array_column($items, $key);
            } else {
                foreach($items as $item) {
                    if (isset($item[$key])) {
                        $array[] = $item[$key];
                    }
                }
            }
        }

        return $this->returnArray($array);
    }

    public function walk($callable, $data = null)
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            array_walk($copy, $callable, $data);

            parent::exchangeArray($copy);
        }

        return $this;
    }

    public function shuffle()
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            shuffle($copy);

            parent::exchangeArray($copy);
        }

        return $this;
    }

    public function sort($flags = SORT_REGULAR)
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            sort($copy, $flags);

            parent::exchangeArray($copy);
        }

        return $this;
    }

    public function arsort($flags = SORT_REGULAR)
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            arsort($copy, $flags);

            parent::exchangeArray($copy);
        }

        return $this;
    }

    public function krsort($flags = SORT_REGULAR)
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            krsort($copy, $flags);

            parent::exchangeArray($copy);
        }

        return $this;
    }

    public function keys()
    {
        return $this->returnArray(array_keys(parent::getArrayCopy()));
    }

    public function values()
    {
        return $this->returnArray(array_values(parent::getArrayCopy()));
    }
}
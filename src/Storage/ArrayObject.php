<?php

namespace Greg\Storage;

use Greg\Engine\Internal;
use Greg\Support\Arr;

class ArrayObject extends \ArrayObject
{
    use Internal;

    public function __construct($input = array(), $iteratorClass = 'ArrayIterator', $flag = ArrayObject::ARRAY_AS_PROPS)
    {
        parent::__construct(Arr::bring($input), $flag, $iteratorClass);
    }

    protected function fixArray($array)
    {
        return Arr::bring($array);
    }

    protected function returnArray($input)
    {
        return ArrayObject::create($this->appName(), $input);
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
        parent::exchangeArray(array());

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
        $arrays = func_get_args();

        array_unshift($arrays, parent::getArrayCopy());

        return $this->returnArray(call_user_func_array('array_merge', $arrays));
    }

    public function mergeRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, $this->getArrayCopy());

        return $this->returnArray(call_user_func_array('array_merge_recursive', $arrays));
    }

    public function mergePrepend($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = parent::getArrayCopy();

        return $this->returnArray(call_user_func_array('array_merge', $arrays));
    }

    public function mergePrependRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = $this->getArrayCopy();

        return $this->returnArray(call_user_func_array('array_merge_recursive', $arrays));
    }

    public function mergeValues()
    {
        return $this->returnArray(call_user_func_array('array_merge', parent::getArrayCopy()));
    }

    public function selfMerge($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, parent::getArrayCopy());

        parent::exchangeArray(call_user_func_array('array_merge', $arrays));

        return $this;
    }

    public function selfMergeRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, $this->getArrayCopy());

        $this->exchangeArray(call_user_func_array('array_merge_recursive', $arrays));

        return $this;
    }

    public function selfMergePrepend($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = parent::getArrayCopy();

        parent::exchangeArray(call_user_func_array('array_merge', $arrays));

        return $this;
    }

    public function selfMergePrependRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = $this->getArrayCopy();

        $this->exchangeArray(call_user_func_array('array_merge_recursive', $arrays));

        return $this;
    }

    public function selfMergeValues()
    {
        parent::exchangeArray(call_user_func_array('array_merge', parent::getArrayCopy()));

        return $this;
    }

    public function replace($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, parent::getArrayCopy());

        return $this->returnArray(call_user_func_array('array_replace', $arrays));
    }

    public function replaceRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, $this->getArrayCopy());

        return $this->returnArray(call_user_func_array('array_replace_recursive', $arrays));
    }

    public function replacePrepend($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = parent::getArrayCopy();

        return $this->returnArray(call_user_func_array('array_replace', $arrays));
    }

    public function replacePrependRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = $this->getArrayCopy();

        return $this->returnArray(call_user_func_array('array_replace_recursive', $arrays));
    }

    public function replaceValues()
    {
        return $this->returnArray(call_user_func_array('array_replace', parent::getArrayCopy()));
    }

    public function selfReplace($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, parent::getArrayCopy());

        parent::exchangeArray(call_user_func_array('array_replace', $arrays));

        return $this;
    }

    public function selfReplaceRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, $this->getArrayCopy());

        $this->exchangeArray(call_user_func_array('array_replace_recursive', $arrays));

        return $this;
    }

    public function selfReplacePrepend($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = parent::getArrayCopy();

        parent::exchangeArray(call_user_func_array('array_replace', $arrays));

        return $this;
    }

    public function selfReplacePrependRecursive($array, $_ = null)
    {
        $arrays = func_get_args();

        $arrays = array_reverse($arrays);

        $arrays[] = $this->getArrayCopy();

        $this->exchangeArray(call_user_func_array('array_replace_recursive', $arrays));

        return $this;
    }

    public function selfReplaceValues()
    {
        parent::exchangeArray(call_user_func_array('array_replace', parent::getArrayCopy()));

        return $this;
    }

    public function diff($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, $array);

        return $this->returnArray(call_user_func_array('array_diff', $arrays));
    }

    public function selfDiff($array, $_ = null)
    {
        $arrays = func_get_args();

        array_unshift($arrays, $array);

        parent::exchangeArray(call_user_func_array('array_diff', $arrays));

        return $this;
    }

    public function map($callback, $_ = null)
    {
        return $this->returnArray(Arr::map(parent::getArrayCopy(), func_get_args()));
    }

    public function mapRecursive($callback, $_ = null)
    {
        return $this->returnArray(Arr::mapRecursive($this->getArrayCopy(), func_get_args()));
    }

    public function selfMap($callback, $_ = null)
    {
        parent::exchangeArray(Arr::map(parent::getArrayCopy(), func_get_args()));

        return $this;
    }

    public function selfMapRecursive($callback, $_ = null)
    {
        $this->exchangeArray(Arr::mapRecursive($this->getArrayCopy(), func_get_args()));

        return $this;
    }

    public function filter($callback = null, $flag = 0)
    {
        return $this->returnArray(Arr::filter(parent::getArrayCopy(), func_get_args()));
    }

    public function filterRecursive($callback = null, $flag = 0)
    {
        return $this->returnArray(Arr::filterRecursive($this->getArrayCopy(), func_get_args()));
    }

    public function selfFilter($callback = null, $flag = 0)
    {
        parent::exchangeArray(Arr::filter(parent::getArrayCopy(), func_get_args()));

        return $this;
    }

    public function selfFilterRecursive($callback = null, $flag = 0)
    {
        $this->exchangeArray(Arr::filterRecursive($this->getArrayCopy(), func_get_args()));

        return $this;
    }

    public function reverse($preserveKeys = false)
    {
        return $this->returnArray(Arr::reverse(parent::getArrayCopy(), func_get_args()));
    }

    public function selfReverse($preserveKeys = false)
    {
        parent::exchangeArray(Arr::reverse(parent::getArrayCopy(), func_get_args()));

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
        $array = array();
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

    public function walk($callback, $data = null)
    {
        if ($this->count()) {
            $copy = parent::getArrayCopy();

            array_walk($copy, $callback, $data);

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
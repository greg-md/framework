<?php

namespace Greg\Html\Head;

use Greg\Engine\Internal;
use Greg\Html\Element;

class Link
{
    use Internal;

    protected $storage = [];

    public function set($rel, $href, array $attr = [], $id = null)
    {
        $attr['rel'] = $rel;

        $attr['href'] = $href;

        if (func_num_args() >= 4) {
            $this->storage[$id] = $attr;
        } else {
            $this->storage[] = $attr;
        }

        return $this;
    }

    public function icon($href, $mime, array $attr = [], $id = null)
    {
        $attr['mime'] = $mime;
        return $this->set(__FUNCTION__, $href, $attr, $id);
    }

    public function __call($method, $args)
    {
        array_unshift($args, $method);

        return call_user_func_array([$this, 'set'], $args);
    }

    public function fetch()
    {
        $items = [];

        foreach($this->storage as $id => $attr) {
            $items[$id] = $this->fetchItem($attr);
        }

        return $items;
    }

    public function fetchItem($attr)
    {
        return new Element('link', $attr);
    }

    public function __toString()
    {
        return implode("\n", $this->fetch());
    }
}
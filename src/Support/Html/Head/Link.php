<?php

namespace Greg\Support\Html\Head;

use Greg\Support\Html\Element;
use Greg\Support\Storage\AccessorTrait;

class Link
{
    use AccessorTrait;

    public function set($rel, $href, array $attr = [], $id = null)
    {
        $attr['rel'] = $rel;

        $attr['href'] = $href;

        if ($id !== null) {
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

    public function style($href, array $attr = [], $id = null)
    {
        $this->set('stylesheet', $href, $attr, $id);
    }

    public function __call($method, $args)
    {
        return $this->set($method, ...$args);
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
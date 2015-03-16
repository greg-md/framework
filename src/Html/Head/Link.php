<?php

namespace Greg\Html\Head;

use Greg\Engine\Internal;
use Greg\Html\Element;
use Greg\Storage\Accessor;

class Link
{
    use Accessor, Internal;

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
        return Element::create($this->appName(), 'link', $attr);
    }

    public function __toString()
    {
        return implode("\n", $this->fetch());
    }
}
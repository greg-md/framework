<?php

namespace Greg\Html;

use Greg\Storage\AccessorTrait;

class HeadLink
{
    use AccessorTrait;

    public function set($rel, $href, array $attr = [], $id = null)
    {
        $attr['rel'] = $rel;

        $attr['href'] = $href;

        $this->setToStorage($id, $attr);

        return $this;
    }

    public function icon($href, $type, array $attr = [], $id = null)
    {
        $attr['type'] = $type;

        return $this->set(__FUNCTION__, $href, $attr, $id);
    }

    public function style($href, array $attr = [], $id = null)
    {
        $this->set('stylesheet', $href, $attr, $id);
    }

    public function toObjects()
    {
        $items = [];

        foreach($this->getStorage() as $id => $attr) {
            $items[$id] = new HtmlElement('link', $attr);
        }

        return $items;
    }

    public function toString()
    {
        return implode("\n", $this->toObjects());
    }

    public function __call($method, $args)
    {
        return $this->set($method, ...$args);
    }

    public function __toString()
    {
        return $this->toString();
    }
}
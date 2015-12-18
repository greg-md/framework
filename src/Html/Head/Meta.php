<?php

namespace Greg\Html\Head;

use Greg\Html\Element;
use Greg\Storage\AccessorTrait;

class Meta
{
    use AccessorTrait;

    public function name($name, $content = null)
    {
        return $this->storage($name, ...(func_num_args() > 1 ? [[
            'name' => $name,
            'content' => $this->clear($content),
        ]] : []));
    }

    public function property($name, $content = null)
    {
        return $this->storage($name, ...(func_num_args() > 1 ? [[
            'property' => $name,
            'content' => $this->clear($content),
        ]] : []));
    }

    public function httpEquiv($name, $content = null)
    {
        return $this->storage($name, ...(func_num_args() > 1 ? [[
            'http-equiv' => $name,
            'content' => $this->clear($content),
        ]] : []));
    }

    public function charset($charset = null)
    {
        return $this->storage('charset', ...(func_num_args() ? [[
            'charset' => $charset,
        ]] : []));
    }

    public function refresh($timeout = 0, $url = null)
    {
        $args = [__FUNCTION__];

        if (func_num_args()) {
            $content = [
                $timeout,
            ];

            if ($url) {
                $content[] = 'url=' . $url;
            }

            $args[] = implode('; ', $content);
        }

        return $this->httpEquiv(...$args);
    }

    public function author($name = null)
    {
        return $this->name(__FUNCTION__, ...func_get_args());
    }

    public function description($content = null)
    {
        return $this->name(__FUNCTION__, ...func_get_args());
    }

    public function generator($name = null)
    {
        return $this->name(__FUNCTION__, ...func_get_args());
    }

    public function keywords($content = null)
    {
        return $this->name(__FUNCTION__, ...func_get_args());
    }

    public function viewPort($name = null)
    {
        return $this->name('viewport', ...func_get_args());
    }

    static public function clear($content)
    {
        return htmlspecialchars(preg_replace('#\n+#', ' ', trim(strip_tags($content))));
    }

    public function toString()
    {
        $items = [];

        foreach($this->storage as $id => $attr) {
            $items[$id] = $this->fetchItem($attr);
        }

        return $items;
    }

    public function fetchItem($attr)
    {
        return new Element('meta', $attr);
    }

    public function __toString()
    {
        return implode("\n", $this->toString());
    }
}
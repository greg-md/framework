<?php

namespace Greg\Html\Head;

use Greg\Engine\Internal;
use Greg\Html\Element;
use Greg\Support\Obj;

class Meta
{
    use Internal;

    protected $storage = [];

    public function name($name, $content = null)
    {
        return func_num_args() > 1 ? $this->storage($name, [
            'name' => $name,
            'content' => $this->clear($content),
        ]) : $this->storage($name);
    }

    public function property($name, $content = null)
    {
        return func_num_args() > 1 ? $this->storage($name, [
            'property' => $name,
            'content' => $this->clear($content),
        ]) : $this->storage($name);
    }

    public function httpEquiv($name, $content = null)
    {
        return func_num_args() > 1 ? $this->storage($name, [
            'http-equiv' => $name,
            'content' => $this->clear($content),
        ]) : $this->storage($name);
    }

    public function charset($charset = null)
    {
        return func_num_args() ? $this->storage('charset', [
            'charset' => $charset,
        ]) : $this->storage('charset');
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
        return Element::create($this->appName(), 'meta', $attr);
    }

    protected function storage($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function __toString()
    {
        return implode("\n", $this->toString());
    }
}
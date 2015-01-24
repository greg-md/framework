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
        return func_num_args() ? $this->storage('charset', ['charset' => $charset]) : $this->storage('charset');
    }

    public function refresh()
    {
        $args = func_get_args();
        array_unshift($args, __FUNCTION__);
        return call_user_func_array([$this, 'httpEquiv'], $args);
    }

    public function author()
    {
        $args = func_get_args();
        array_unshift($args, __FUNCTION__);
        return call_user_func_array([$this, 'name'], $args);
    }

    public function description()
    {
        $args = func_get_args();
        array_unshift($args, __FUNCTION__);
        return call_user_func_array([$this, 'name'], $args);
    }

    public function generator()
    {
        $args = func_get_args();
        array_unshift($args, __FUNCTION__);
        return call_user_func_array([$this, 'name'], $args);
    }

    public function keywords()
    {
        $args = func_get_args();
        array_unshift($args, __FUNCTION__);
        return call_user_func_array([$this, 'name'], $args);
    }

    public function viewPort()
    {
        $args = func_get_args();
        array_unshift($args, strtolower(__FUNCTION__));
        return call_user_func_array([$this, 'name'], $args);
    }

    static public function clear($content)
    {
        return htmlspecialchars(preg_replace("#\n+#", " ", trim(strip_tags($content))));
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
        return new Element('meta', $attr);
    }

    protected function storage($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function __call($method, $args)
    {
        array_unshift($args, $method);

        return call_user_func_array([$this, 'set'], $args);
    }

    public function __toString()
    {
        return implode("\n", $this->fetch());
    }
}
<?php

namespace Greg\Engine;

trait Manager
{
    protected $storage = null;

    public function __construct($storage)
    {
        if (is_array($storage)) {
            $storage = call_user_func_array([$this->app(), 'newClass'], $storage);
        }

        $this->storage($storage);
    }

    public function __call($method, $args)
    {
        return call_user_func_array([$this->storage(), $method], $args);
    }

    abstract public function app();

    abstract public function storage();
}
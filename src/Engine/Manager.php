<?php

namespace Greg\Engine;

use Greg\Application\Runner;

trait Manager
{
    protected $storage = null;

    public function __construct($storage)
    {
        if (is_scalar($storage)) {
            $storage = [$storage];
        }

        if (is_array($storage)) {
            $storage = $this->app()->loadInstance(...$storage);
        }

        $this->storage($storage);
    }

    public function __call($method, $args)
    {
        return $this->storage()->$method(...$args);
    }

    /**
     * @param Runner $app
     * @return Runner
     */
    abstract public function app(Runner $app = null);

    abstract public function storage();
}
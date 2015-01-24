<?php

namespace Greg\Router;

interface RouteInterface
{
    /**
     * @param $path
     * @return array|bool
     */
    public function fetch($path);

    public function get(array $param = []);
}
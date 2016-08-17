<?php

namespace Greg\Router;

use Greg\Engine\InternalTrait;

class Router
{
    use RouterTrait, InternalTrait;

    public function createRoute($format, $action, array $settings = [])
    {
        return $this->_createRoute($format, $action, $settings)->setRouter($this);
    }
}
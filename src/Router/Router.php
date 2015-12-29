<?php

namespace Greg\Router;

use Greg\Engine\InternalTrait;

class Router
{
    use RouterTrait, InternalTrait;

    public function __construct(array $routes = [], array $onError = [])
    {
        $this->addMore($routes);

        $this->onError($onError);

        return $this;
    }

    /**
     * @param $format
     * @param $action
     * @param array $settings
     * @return Route
     */
    public function createRoute($format, $action, array $settings = [])
    {
        return $this->_createRoute($format, $action, $settings)->router($this);
    }
}
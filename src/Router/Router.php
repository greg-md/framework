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
     * @param string $name
     * @param string $format
     * @param null $type
     * @param callable|array $settings
     * @return Route
     */
    public function createRoute($name, $format, $type = null, $settings = null)
    {
        return $this->_createRoute($name, $format, $type, $settings)->router($this);
    }
}
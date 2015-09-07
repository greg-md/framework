<?php

namespace Greg\Router;

use Greg\Engine\InternalTrait;

/**
 * Class Router
 * @package Greg\Router
 *
 * @method Route createRoute($name, $format, $type = null, $settings = null)
 */
class Router extends \Greg\Support\Router\Router implements RouterInterface
{
    use RouterTrait, InternalTrait;

    static public function create($appName, array $routes = [], array $onError = [])
    {
        return static::newInstanceRef($appName, $routes, $onError);
    }
}
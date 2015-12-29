<?php

namespace Greg\Application\Router;

use Greg\Application\Engine\InternalTrait;

/**
 * Class Router
 * @package Greg\Router
 *
 * @method Route createRoute($format, $action, array $settings = null)
 */
class Router extends \Greg\Router\Router implements RouterInterface
{
    use RouterTrait, InternalTrait;

    static public function create($appName, array $routes = [], array $onError = [])
    {
        return static::newInstanceRef($appName, $routes, $onError);
    }
}
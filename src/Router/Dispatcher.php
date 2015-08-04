<?php

namespace Greg\Router;

use Greg\Engine\InternalTrait;

/**
 * Class Dispatcher
 * @package Greg\Router
 *
 * @method Route createRoute($name, $format, $type = null, $settings = null)
 */
class Dispatcher extends \Greg\Support\Router\Dispatcher implements RouterInterface
{
    use RouterTrait, InternalTrait;

    static public function create($appName, array $routes = [])
    {
        return static::newInstanceRef($appName, $routes);
    }
}
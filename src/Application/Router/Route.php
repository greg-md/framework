<?php

namespace Greg\Application\Router;

use Greg\Application\Engine\InternalTrait;
use Greg\Application\Http\Request;
use Greg\Tool\Obj;
use Greg\Tool\Str;

class Route extends \Greg\Router\Route implements RouterInterface
{
    use RouterTrait, InternalTrait;

    static public function create($appName, $format, $action, array $settings = [])
    {
        return static::newInstanceRef($appName, $format, $action, $settings);
    }

    protected function fetchMiddleware($middleware)
    {
        if (!is_object($middleware)) {
            $middleware = $this->app()->binder()->getExpected($middleware);
        }

        return $middleware;
    }

    public function dispatchAction($action, array $params = [])
    {
        $routeParams = $this->lastMatchedParams();

        $allParams = $routeParams + $params;

        if (is_callable($action)) {
            $request = Request::create($this->appName(), $allParams);

            return $this->callCallableWith($action, $request, ...array_values($allParams), ...[$this]);
        } else {
            list($controller, $action) = explode('@', $action);

            $controller = Str::spinalCase($controller);

            $action = Str::spinalCase($action);

            $allParams += [
                'controller' => $controller,
                'action' => $action,
            ];

            return $this->app()->action($action, $controller, $allParams, $this);
        }
    }
}
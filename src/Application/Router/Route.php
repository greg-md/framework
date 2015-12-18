<?php

namespace Greg\Application\Router;

use Greg\Application\Engine\InternalTrait;
use Greg\Application\Http\Request;
use Greg\Tool\Obj;
use Greg\Tool\Str;

class Route extends \Greg\Router\Route implements RouterInterface
{
    use RouterTrait, InternalTrait;

    protected $action = null;

    static public function create($appName, $name, $format, $type = null, callable $callback = null)
    {
        return static::newInstanceRef($appName, $name, $format, $type, $callback);
    }

    public function dispatch(array $params = [])
    {
        try {
            $routeParams = $this->lastMatchedParams();

            $allParams = $routeParams + $params;

            if ($callback = $this->callback()) {
                $request = Request::create($this->appName(), $allParams);

                return $this->callCallableWith($callback, $request, ...array_values($allParams), ...[$this]);
            }

            if ($action = $this->action()) {
                list($controller, $action) = explode('@', $action);

                $controller = Str::spinalCase($controller);

                $action = Str::spinalCase($action);

                $allParams += [
                    'controller' => $controller,
                    'action' => $action,
                ];

                return $this->app()->action($action, $controller, $allParams, $this);
            }
        } catch (\Exception $e) {
            return $this->dispatchException($e);
        }

        return null;
    }

    public function action($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
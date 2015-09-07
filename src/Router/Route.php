<?php

namespace Greg\Router;

use Greg\Engine\InternalTrait;
use Greg\Support\Tool\Obj;
use Greg\Support\Tool\Str;

class Route extends \Greg\Support\Router\Route implements RouterInterface
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
            if ($callback = $this->callback()) {
                return $this->callCallable($callback, $params + $this->lastMatchedParams(), $this);
            }

            if ($action = $this->action()) {
                list($controller, $action) = explode('@', $action);

                $controller = Str::spinalCase($controller);

                $action = Str::spinalCase($action);

                $params = [
                        'controller' => $controller,
                        'action' => $action,
                    ] + $params + $this->lastMatchedParams();

                return $this->app()->action($action, $controller, $params);
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
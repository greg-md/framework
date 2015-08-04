<?php

namespace Greg\Support\Engine;

use Greg\Support\Tool\Debug;
use Greg\Support\Tool\Obj;

trait InternalTrait
{
    protected function callCallable(callable $callable, ...$args)
    {
        return call_user_func_array($callable, $args);
    }

    protected function callCallableWith(callable $callable, ...$args)
    {
        return Obj::callWithArgs($callable, $args);
    }

    protected function loadClassInstance($className, ...$args)
    {
        return Obj::loadInstanceArgs($className, $args);
    }

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this));
    }
}
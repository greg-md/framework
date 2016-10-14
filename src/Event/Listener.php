<?php

namespace Greg\Event;

use Greg\Support\Accessor\AccessorTrait;
use Greg\Support\IoC\IoCManagerAccessorTrait;
use Greg\Support\Obj;
use Greg\Support\Str;

class Listener implements ListenerInterface
{
    use AccessorTrait, IoCManagerAccessorTrait;

    protected $callCallable = null;

    protected $callCallableWith = null;

    public function on($eventName, callable $callable)
    {
        $this->accessor[$eventName][] = $callable;

        return $this;
    }

    public function register($eventName, $object)
    {
        if (!is_object($object)) {
            throw new \Exception('Event registrar should be an object.');
        }

        foreach ((array) $eventName as $eName) {
            $method = lcfirst(Str::phpName($eName));

            if (!method_exists($object, $method)) {
                throw new \Exception('Method `' . $method . '` not found in class `' . get_class($object) . '`.');
            }

            $this->on($eName, [$object, $method]);
        }

        return $this;
    }

    public function fire($eventName, ...$args)
    {
        return $this->fireRef($eventName, ...$args);
    }

    public function fireRef($eventName, &...$args)
    {
        return $this->fireArgs($eventName, $args);
    }

    public function fireArgs($eventName, array $args = [])
    {
        foreach ((array) $this->getFromAccessor($eventName) as $function) {
            $this->callCallable($function, ...$args);
        }

        return $this;
    }

    public function fireWith($eventName, ...$args)
    {
        return $this->fireWithRef($eventName, ...$args);
    }

    public function fireWithRef($eventName, &...$args)
    {
        return $this->fireWithArgs($eventName, $args);
    }

    public function fireWithArgs($eventName, array $args = [])
    {
        foreach ((array) $this->getFromAccessor($eventName) as $function) {
            $this->callCallableWith($function, ...$args);
        }

        return $this;
    }

    public function setCallCallable(callable $callable)
    {
        $this->callCallable = $callable;

        return $this;
    }

    public function getCallCallable()
    {
        return $this->callCallable;
    }

    public function setCallCallableWith(callable $callable)
    {
        $this->callCallableWith = $callable;

        return $this;
    }

    public function getCallCallableWith()
    {
        return $this->callCallableWith;
    }

    protected function callCallable(callable $callable, ...$args)
    {
        if ($callCallable = $this->getCallCallable()) {
            return Obj::callCallable($callCallable, ...func_get_args());
        }

        return Obj::callCallable(...func_get_args());
    }

    protected function callCallableWith(callable $callable, ...$args)
    {
        if ($callCallableWith = $this->getCallCallableWith()) {
            return Obj::callCallable($callCallableWith, ...func_get_args());
        }

        return Obj::callCallableWith(...func_get_args());
    }
}

<?php

namespace Greg\Engine;

use Greg\Tool\Arr;
use Greg\Tool\Obj;

class Binder
{
    use InternalTrait;

    protected $storage = [];

    protected $singletons = [];

    public function loadInstance($className, ...$args)
    {
        return $this->loadInstanceArgs($className, $args);
    }

    public function loadInstanceArgs($className, array $args = [])
    {
        $class = new \ReflectionClass($className);

        $self = $class->newInstanceWithoutConstructor();

        method_exists($self, '__bind') && $this->call([$self, '__bind']);

        if ($constructor = $class->getConstructor()) {
            if ($expectedArgs = $constructor->getParameters()) {
                $args = $this->addExpectedArgs($args, $expectedArgs);
            }

            $constructor->invokeArgs($self, $args);
        }

        return $self;
    }

    public function call(callable $callable, ...$args)
    {
        return $this->callRef($callable, ...$args);
    }

    public function callRef(callable $callable, &...$args)
    {
        return $this->callArgs($callable, $args);
    }

    public function callArgs(callable $callable, array $args = [])
    {
        return call_user_func_array($callable, $this->getCallableArgs($callable, $args));
    }

    public function callWith(callable $callable, ...$args)
    {
        return $this->callWithRef($callable, ...$args);
    }

    public function callWithRef(callable $callable, &...$args)
    {
        return $this->callWithArgs($callable, $args);
    }

    public function callWithArgs(callable $callable, array $args = [])
    {
        return call_user_func_array($callable, $this->getCallableMixedArgs($callable, $args));
    }

    public function getCallableArgs(callable $callable, array $args = [])
    {
        if ($expectedArgs = Obj::expectedArgs($callable)) {
            return $this->addExpectedArgs($args, $expectedArgs);
        }

        return [];
    }

    public function getCallableMixedArgs(callable $callable, array $args = [])
    {
        if ($expectedArgs = Obj::expectedArgs($callable)) {
            return Obj::fetchExpectedArgs($expectedArgs, $args, function(\ReflectionParameter $expectedArg) {
                return $this->expectedArg($expectedArg);
            }, true);
        }

        return [];
    }

    public function addExpectedArgs(array $args, array $expectedArgs)
    {
        /* @var $expectedArgs \ReflectionParameter[] */

        if ($args) {
            $expectedArgs = array_slice($expectedArgs, sizeof($args));
        }

        $newArgs = Obj::fetchExpectedArgs($expectedArgs, [], function(\ReflectionParameter $expectedArg) {
            return $this->expectedArg($expectedArg);
        });

        if ($newArgs) {
            $args = array_merge($args, $newArgs);
        }

        return $args;
    }

    public function expectedArg(\ReflectionParameter $expectedArg)
    {
        if ($expectedType = $expectedArg->getClass()) {
            $className = $expectedType->getName();

            $arg = $this->get($className);

            if (!$arg and !$expectedArg->isOptional()) {
                throw new \Exception('Object `' . $className . '` is not registered in binder.');
            }
        } else {
            $arg = Obj::expectedArg($expectedArg);
        }

        return $arg;
    }

    public function setObjects(array $objects)
    {
        foreach($objects as $object) {
            $this->setObject($object);
        }

        return $this;
    }

    public function setObject($object)
    {
        return $this->set(get_class($object), $object);
    }

    public function set($name, $object)
    {
        if (!is_object($object)) {
            throw new \Exception('Item is not an object.');
        }

        if ($this->storage($name)) {
            throw new \Exception('Object `' . $name . '` is already in use in binder.');
        }

        $this->storage($name, $object);

        return $this;
    }

    public function getExpected($name)
    {
        if (!$object = $this->get($name)) {
            throw new \Exception('Object `' . $name . '` is not registered in binder.');
        }

        return $object;
    }

    public function get($name)
    {
        $object = $this->storage($name);

        if (!$object and $instance = $this->singletons($name)) {
            if (is_callable($instance)) {
                $object = $this->call($instance);
            } else {
                $instance = Arr::bring($instance);

                $object = $this->loadClassInstance(...$instance);
            }

            $this->storage($name, $object);
        }

        return $object;
    }

    public function storage($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function singletons($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
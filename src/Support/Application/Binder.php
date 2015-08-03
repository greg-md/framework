<?php

namespace Greg\Support\Application;

use Greg\Support\Arr;
use Greg\Support\Obj;

class Binder
{
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
        if ($expectedArgs = Obj::expectedArgs($callable)) {
            $args = $this->addExpectedArgs($args, $expectedArgs);
        }

        return call_user_func_array($callable, $args);
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
        $funcArgs = [];

        if ($expectedArgs = Obj::expectedArgs($callable)) {
            $funcArgs = Obj::fetchExpectedArgs($expectedArgs, $args, function(\ReflectionParameter $expectedArg) {
                return $this->expectedArg($expectedArg);
            });
        }

        return call_user_func_array($callable, $funcArgs);
    }

    public function expectedArg(\ReflectionParameter $expectedArg)
    {
        if ($expectedType = $expectedArg->getClass()) {
            $className = $expectedType->getName();

            $arg = $this->get($className);

            if (!$arg and !$expectedArg->isOptional()) {
                throw new \Exception('`' . $className . '` is not registered in binder.');
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
            throw new \Exception('Object name `' . $name . '` is already in use in binder.');
        }

        $this->storage($name, $object);

        return $this;
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

    protected function loadClassInstance($className, ...$args)
    {
        return Obj::loadInstanceArgs($className, ...$args);
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
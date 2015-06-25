<?php

namespace Greg\Application;

use Greg\Support\Engine\Internal;
use Greg\Support\Arr;
use Greg\Support\Obj;

class Binder
{
    use Internal;

    protected $storage = [];

    protected $singletons = [];

    //protected $adapters = [];

    protected $instancesPrefixes = [];

    /*
    public function __construct(array $storage = [], array $instancesPrefixes = [])
    {
        $this->storage($storage);

        //$this->instancesPrefixes($instancesPrefixes);

        return $this;
    }
    */

    static public function create($appName)
    {
        return static::newInstanceRef($appName);
    }

    /*
    public function addAdapter(callable $adapter)
    {
        $this->adapters[] = $adapter;

        return $this;
    }

    public function findInAdapters($className)
    {
        foreach($this->adapters() as $adapter) {
            if ($class = $adapter($className)) {
                return $class;
            }
        }

        return false;
    }
    */

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

        if ($newArgs = $this->fetchExpectedArgs($expectedArgs)) {
            $args = array_merge($args, $newArgs);
        }

        return $args;
    }

    public function fetchExpectedArgs(array $expectedArgs, array $customArgs = [])
    {
        $assocArgs = [];

        foreach($customArgs as $key => $value) {
            if (is_int($key)) {
                $assocArgs[get_class($value)] = $value;
            } else {
                $assocArgs[$key] = $value;
            }
        }

        /* @var $expectedArgs \ReflectionParameter[] */
        $expectedArgs = array_reverse($expectedArgs);

        $returnArgs = [];

        foreach ($expectedArgs as $expectedArg) {
            if (!$returnArgs and !$expectedArg->getClass() and $expectedArg->isOptional()) {
                continue;
            }

            if ($assocArgs and $expectedType = $expectedArg->getClass() and Arr::has($assocArgs, $expectedType->getName())) {
                $returnArgs[] = $assocArgs[$expectedType->getName()];
            } else {
                $returnArgs[] = $this->expectedArg($expectedArg);
            }
        }

        $returnArgs = array_reverse($returnArgs);

        return $returnArgs;
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
            $funcArgs = $this->fetchExpectedArgs($expectedArgs, $args);
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
            if (!$expectedArg->isOptional()) {
                throw new \Exception('Argument `' . $expectedArg->getName() . '` is required in `'
                    . $expectedArg->getDeclaringClass() . '::' . $expectedArg->getDeclaringFunction() . '`');
            }

            $arg = $expectedArg->getDefaultValue();
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

                $object = $this->app()->loadInstance(...$instance);
            }

            $this->storage($name, $object);
        }

        /*
        if (!$object) {
            $object = $this->findInAdapters($name);
        }
        */

        if (!$object and $this->isInstancePrefix($name)) {
            /* @var $name Internal */
            $object = $name::instance($this->appName());
        }

        return $object;
    }

    public function isInstancePrefix($className)
    {
        $array = $this->instancesPrefixes();

        return Arr::has($array, function($value) use ($className) {
            return strpos($className, $value) === 0;
        });
    }

    public function storage($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function singletons($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function instancesPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
    /*
    public function adapters($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
    */
}
<?php

namespace Greg\Application;

use Greg\Engine\Internal;
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
        if (is_scalar($callable) and strpos($callable, '::')) {
            $callable = explode('::', $callable, 2);
        }

        if (is_array($callable)) {
            $expectedArgs = (new \ReflectionMethod($callable[0], $callable[1]))->getParameters();
        } else {
            $expectedArgs = (new \ReflectionFunction($callable))->getParameters();
        }

        if ($expectedArgs) {
            $args = $this->addExpectedArgs($args, $expectedArgs);
        }

        return call_user_func_array($callable, $args);
    }

    public function addExpectedArgs($args, $expectedArgs)
    {
        if ($args) {
            $expectedArgs = array_slice($expectedArgs, sizeof($args));
        }

        if ($expectedArgs) {
            $newArgs = [];

            /* @var $expectedArg \ReflectionParameter */
            foreach (array_reverse($expectedArgs) as $expectedArg) {
                if (!$newArgs and !$expectedArg->getClass() and $expectedArg->isOptional()) {
                    continue;
                }

                $newArgs[] = $this->expectedArg($expectedArg);
            }

            if ($newArgs) {
                $args = array_merge($args, array_reverse($newArgs));
            }
        }

        return $args;
    }

    public function expectedArg(\ReflectionParameter $expectedArg)
    {
        $expectedType = $expectedArg->getClass();

        if ($expectedType) {
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
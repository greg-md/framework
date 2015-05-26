<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Arr;
use Greg\Support\Obj;

class Binder implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    protected $adapters = [];

    protected $instancesPrefixes = [];

    public function __construct(array $objects = [], array $instancesPrefixes = [])
    {
        $this->storage($objects);

        $this->instancesPrefixes($instancesPrefixes);

        return $this;
    }

    static public function create($appName, array $objects = [], array $instancesPrefixes = [])
    {
        return static::newInstanceRef($appName, $objects, $instancesPrefixes);
    }

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

    public function loadInstance($className, ...$args)
    {
        return $this->loadInstanceArgs($className, $args);
    }

    public function loadInstanceArgs($className, array $args = [])
    {
        $class = new \ReflectionClass($className);

        $self = $class->newInstanceWithoutConstructor();

        if (method_exists($self, '__bind')) {
            $this->call([$self, '__bind']);
        }

        if ($constructor = $class->getConstructor()) {
            $expectedArgs = $constructor->getParameters();

            if ($expectedArgs) {
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

            if (!$arg) {
                $arg = $this->findInAdapters($className);
            }

            if (!$arg and $this->isInstancePrefix($className)) {
                $arg = $className;
            }

            if (!$arg and !$expectedArg->isOptional()) {
                throw new \Exception('`' . $className . '` is not registered in binder.');
            }

            if (!is_object($arg)) {
                $arg = $arg::instance($this->appName());
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

    public function isInstancePrefix($className)
    {
        $array = $this->instancesPrefixes();

        return Arr::has($array, function($value) use ($className) {
            return strpos($className, $value) === 0;
        });
    }

    public function instancesPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function adapters($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
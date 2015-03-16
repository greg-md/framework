<?php

namespace Greg\Application;

use Greg\Application\Binder\Adapter;
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

    public function __construct(array $objects = [])
    {
        $this->storage($objects);

        return $this;
    }

    static public function create($appName, array $objects = [])
    {
        return static::newInstanceRef($appName, $objects);
    }

    public function addAdapter($name, callable $caller, $storage)
    {
        $this->adapters[$name] = new Adapter($caller, $storage);

        return $this;
    }

    public function findInAdapters($className)
    {
        /* @var $adapter Adapter */
        foreach($this->adapters as $adapter) {
            if ($adapter->has($className)) {
                return $adapter;
            }
        }

        return false;
    }

    /* We enabled to set and strings which will get an instance of the objects
    public function set($name, $object)
    {
        if (!is_object($object)) {
            throw Exception::newInstance($this->appName(), 'You can set only objects in binder.');
        }

        Arr::set($this->accessor(), $name, $object);

        return $this;
    }
    */

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
                $found = false;

                if (($adapter = $this->findInAdapters($className))) {
                    $arg = $this->callArgs($adapter->caller(), $adapter->getArray($className));

                    $found = true;
                } elseif ($this->isInstancePrefix($className)) {
                    /* @var $className string|Internal */
                    $arg = $className::instance($this->appName());

                    $found = true;
                }

                if (!$found) {
                    throw Exception::newInstance($this->appName(), '`' . $className . '` is not registered in binder.');
                }
            }

            if (!is_object($arg)) {
                $arg = $arg::instance($this->appName());
            }
        } else {
            if (!$expectedArg->isOptional()) {
                throw Exception::newInstance($this->appName(), 'Argument `' . $expectedArg->getName() . '` is required in `'
                    . $expectedArg->getDeclaringClass() . '::' . $expectedArg->getDeclaringFunction() . '`');
            }

            $arg = $expectedArg->getDefaultValue();
        }

        return $arg;
    }

    public function isInstancePrefix($className)
    {
        return Arr::has($this->instancesPrefixes, function($value) use ($className) {
            return strpos($className, $value) === 0;
        });
    }

    public function instancesPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
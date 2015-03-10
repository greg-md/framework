<?php

namespace Greg\Application;

use Greg\Application\Binder\Adapter;
use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Binder implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    protected $adapters = [];

    protected $instancesPrefixes = [];

    public function addAdapter($name, callable $callable, $storage)
    {
        $this->adapters[$name] = new Adapter($callable, $storage);

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

    public function add($class)
    {
        return $this->set(get_class($class), $class);
    }

    public function newInstance($className, ...$args)
    {
        return $this->newInstanceArgs($className, $args);
    }

    public function newInstanceArgs($className, array $args = [])
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

    public function call($function, ...$args)
    {
        return $this->callArgs($function, $args);
    }

    public function callArgs($function, array $args = [])
    {
        if (is_scalar($function) and strpos($function, '::')) {
            $function = explode('::', $function, 2);
        }

        if (is_array($function)) {
            $expectedArgs = (new \ReflectionMethod($function[0], $function[1]))->getParameters();
        } elseif (is_callable($function) or is_scalar($function)) {
            $expectedArgs = (new \ReflectionFunction($function))->getParameters();
        } else {
            $expectedArgs = [];
        }

        if ($expectedArgs) {
            $args = $this->addExpectedArgs($args, $expectedArgs);
        }

        return call_user_func_array($function, $args);
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
                    $arg = $this->callArgs($adapter->caller(), (array)$adapter->get($className));

                    $found = true;
                }

                if (!$found and $this->instancesPrefixes()->has(function($value) use ($className) {
                    return strpos($className, $value) === 0;
                })) {
                    /* @var $className string|Internal */
                    $arg = $className::instance();

                    $found = true;
                }

                if (!$found) {
                    throw Exception::create($this->appName(), '`' . $className . '` is not registered in binder.');
                }
            }
        } else {
            if (!$expectedArg->isOptional()) {
                throw Exception::create($this->appName(), 'Argument `' . $expectedArg->getName() . '` is required in `'
                    . $expectedArg->getDeclaringClass() . '::' . $expectedArg->getDeclaringFunction() . '`');
            }

            $arg = $expectedArg->getDefaultValue();
        }

        return $arg;
    }

    public function instancesPrefixes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayObjVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
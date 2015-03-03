<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Binder implements \ArrayAccess, InternalInterface
{
    use ArrayAccess, Internal;

    protected $adapters = [];

    protected $instancesPrefixes = [];

    public function addAdapter($name, $callback, $storage)
    {
        $this->adapters[$name] = [
            'callback' => $callback,
            'storage' => $storage,
        ];
    }

    public function findInAdapters($className)
    {
        foreach($this->adapters as $adapter) {
            if (isset($adapter['storage'][$className])) {
                return [$adapter['callback'], $adapter['storage'][$className]];
            }
        }

        return false;
    }

    public function add($class)
    {
        return $this->set(get_class($class), $class);
    }

    public function find($class)
    {
        return $this->get($class);
    }

    public function newClass($className, array $args = [])
    {
        $class = new \ReflectionClass($className);

        if ($class->hasMethod('__construct')) {
            $expectedArgs = $class->getMethod('__construct')->getParameters();

            if ($expectedArgs) {
                $args = $this->addExpectedArgs($args, $expectedArgs);
            }
        } else {
            $args = [];
        }

        return $class->newInstanceArgs($args);
    }

    public function call($function, array $args = [])
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

    protected function addExpectedArgs($args, $expectedArgs)
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

            $arg = $this->find($className);

            if (!$arg and !$expectedArg->isOptional()) {
                $found = false;

                if (($adapter = $this->findInAdapters($className))) {
                    list($callback, $args) = $adapter;

                    $arg = $this->call($callback, (array)$args);

                    $found = true;
                }

                if ($this->instancesPrefixes()->has(function($value) use ($className) {
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

    public function instancesPrefixes($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayObjVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
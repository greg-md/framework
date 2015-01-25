<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Storage\ArrayAccess;

class Binder implements \ArrayAccess
{
    use ArrayAccess, Internal;

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
                throw Exception::create($this->appName(), '`' . $className . '` is not registered in binder.');
                //$arg = $className::create($this->appName());
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
}
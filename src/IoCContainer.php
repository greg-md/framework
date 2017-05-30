<?php

namespace Greg\Framework;

use Greg\Support\Obj;

class IoCContainer
{
    private $prefixes = [];

    private $storage = [];

    private $concrete = [];

    public function __construct(array $prefixes = [])
    {
        $this->setPrefixes($prefixes);

        return $this;
    }

    public function load(string $className, ...$arguments)
    {
        $class = new \ReflectionClass($className);

        $self = $class->newInstanceWithoutConstructor();

        if ($constructor = $class->getConstructor()) {
            if ($parameters = $constructor->getParameters()) {
                $arguments = $this->populateParameters($parameters, $arguments);
            }

            $constructor->invokeArgs($self, $arguments);
        }

        return $self;
    }

    public function call(callable $callable, ...$arguments)
    {
        if ($parameters = Obj::parameters($callable)) {
            $arguments = $this->populateParameters($parameters, $arguments);
        }

        return call_user_func_array($callable, $arguments);
    }

    public function callRef(callable $callable, &...$arguments)
    {
        if ($parameters = Obj::parameters($callable)) {
            $arguments = $this->populateParameters($parameters, $arguments);
        }

        return call_user_func_array($callable, $arguments);
    }

    public function callMixed(callable $callable, ...$arguments)
    {
        if ($parameters = Obj::parameters($callable)) {
            $arguments = Obj::populateParameters($parameters, $arguments, true, function (\ReflectionParameter $parameter) {
                return $this->parameterValue($parameter);
            });
        }

        return call_user_func_array($callable, $arguments);
    }

    public function callMixedRef(callable $callable, &...$arguments)
    {
        if ($parameters = Obj::parameters($callable)) {
            $arguments = Obj::populateParameters($parameters, $arguments, true, function (\ReflectionParameter $parameter) {
                return $this->parameterValue($parameter);
            });
        }

        return call_user_func_array($callable, $arguments);
    }

    public function inject($abstract, $loader = null)
    {
        if (array_key_exists($abstract, $this->storage)) {
            throw new \Exception('`' . $abstract . '` is already in use in IoC Container.');
        }

        return $this->register($abstract, null, $loader ?: $abstract);
    }

    public function concrete($abstract, $concrete)
    {
        if (array_key_exists($abstract, $this->storage)) {
            throw new \Exception('`' . $abstract . '` is already in use in IoC Container.');
        }

        return $this->register($abstract, $concrete);
    }

    public function object($object)
    {
        if (!is_object($object)) {
            throw new \Exception('Argument is not an object.');
        }

        return $this->concrete(get_class($object), $object);
    }

    public function get($abstract)
    {
        if (!array_key_exists($abstract, $this->concrete)) {
            if ($item = $this->storage[$abstract] ?? null) {
                if ($loader = $item['loader']) {
                    if (is_callable($loader)) {
                        $this->concrete[$abstract] = $this->call($loader);
                    } elseif (!is_object($loader)) {
                        if (is_array($loader)) {
                            $this->concrete[$abstract] = $this->load(...$loader);
                        } else {
                            $this->concrete[$abstract] = $this->load($loader);
                        }
                    }
                } else {
                    $this->concrete[$abstract] = $this->load($item['concrete']);
                }
            } elseif ($this->prefixIsRegistered($abstract)) {
                $this->concrete[$abstract] = $this->load($abstract);
            }
        }

        return $this->concrete[$abstract] ?? null;
    }

    public function expect($abstract)
    {
        if (!$concrete = $this->get($abstract)) {
            throw new \Exception('`' . $abstract . '` is not registered in IoC Container.');
        }

        return $concrete;
    }

    public function addPrefix($prefix)
    {
        $this->prefixes[] = (string) $prefix;

        $this->fixPrefixes();

        return $this;
    }

    public function addPrefixes(array $prefixes)
    {
        $this->prefixes = array_merge($this->prefixes, $prefixes);

        $this->fixPrefixes();

        return $this;
    }

    public function setPrefixes(array $prefixes)
    {
        $this->prefixes = $prefixes;

        $this->fixPrefixes();

        return $this;
    }

    public function getPrefixes()
    {
        return $this->prefixes;
    }

    protected function prefixIsRegistered($className)
    {
        foreach ($this->prefixes as $prefix) {
            if (strpos($className, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    protected function register($abstract, $concrete = null, $loader = null)
    {
        $this->storage[$abstract] = [
            'concrete' => $concrete,
            'loader'   => $loader,
        ];

        return $this;
    }

    protected function fixPrefixes()
    {
        $this->prefixes = array_unique($this->prefixes);

        return $this;
    }

    protected function populateParameters(array $parameters, array $arguments)
    {
        if ($arguments) {
            $parameters = array_slice($parameters, count($arguments));
        }

        $newArguments = Obj::populateParameters($parameters, [], false, function (\ReflectionParameter $parameter) {
            return $this->parameterValue($parameter);
        });

        if ($newArguments) {
            $arguments = array_merge($arguments, $newArguments);
        }

        return $arguments;
    }

    protected function parameterValue(\ReflectionParameter $parameter)
    {
        if ($expectedType = $parameter->getClass()) {
            $className = $expectedType->getName();

            $arg = $parameter->isOptional() ? $this->get($className) : $this->expect($className);
        } else {
            $arg = Obj::parameterValue($parameter);
        }

        return $arg;
    }
}

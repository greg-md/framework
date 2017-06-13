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

    public function addPrefixes(string $prefix, string ...$prefixes)
    {
        $this->prefixes[] = $prefix;

        if ($prefixes) {
            $this->prefixes = array_merge($this->prefixes, $prefixes);
        }

        $this->fixPrefixes();

        return $this;
    }

    public function inject(string $abstract, $concrete, ...$arguments)
    {
        if (array_key_exists($abstract, $this->storage)) {
            throw new \Exception('`' . $abstract . '` is already in use in IoC Container.');
        }

        if (is_callable($concrete) or (is_string($concrete) and class_exists($concrete, false))) {
            $this->storage[$abstract] = [
                'loader'    => $concrete,
                'arguments' => $arguments,
            ];
        } elseif (is_object($concrete)) {
            if (array_key_exists($abstract, $this->concrete)) {
                throw new \Exception('`' . $abstract . '` is already in use in IoC Container.');
            }

            $this->concrete[$abstract] = $concrete;
        } else {
            throw new \Exception('Unknown concrete type for abstract `' . $abstract . '`.');
        }

        return $this;
    }

    public function register($object)
    {
        if (!is_object($object)) {
            throw new \Exception('Argument is not an object.');
        }

        return $this->inject(get_class($object), $object);
    }

    public function get($abstract)
    {
        if (!array_key_exists($abstract, $this->concrete)) {
            if ($concrete = $this->storage[$abstract] ?? null) {
                if (is_callable($concrete['loader'])) {
                    $this->concrete[$abstract] = $this->call($concrete['loader'], ...$concrete['arguments']);
                } else {
                    $this->concrete[$abstract] = $this->load($concrete['loader'], ...$concrete['arguments']);
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

    public function load(string $className, &...$arguments)
    {
        return $this->loadArgs($className, $arguments);
    }

    public function loadArgs(string $className, array $arguments)
    {
        $class = new \ReflectionClass($className);

        if ($class->isInternal()) {
            $self = $class->newInstanceArgs($arguments);
        } else {
            $self = $class->newInstanceWithoutConstructor();

            if ($constructor = $class->getConstructor()) {
                if ($parameters = $constructor->getParameters()) {
                    $arguments = $this->populateParameters($parameters, $arguments);
                }

                $constructor->invokeArgs($self, $arguments);
            }
        }

        return $self;
    }

    public function &call(callable $callable, &...$arguments)
    {
        return $this->callArgs($callable, $arguments);
    }

    public function &callArgs(callable $callable, array $arguments)
    {
        if ($parameters = Obj::parameters($callable)) {
            $arguments = $this->populateParameters($parameters, $arguments);
        }

        return Obj::callArgs($callable, $arguments);
    }

    private function prefixIsRegistered($className): bool
    {
        foreach ($this->prefixes as $prefix) {
            if (strpos($className, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    private function fixPrefixes()
    {
        $this->prefixes = array_unique($this->prefixes);

        return $this;
    }

    private function populateParameters(array $parameters, array $arguments = [])
    {
        return Obj::populateParameters($parameters, $arguments, function (\ReflectionParameter $parameter) {
            $className = $parameter->getClass()->getName();

            return $parameter->isOptional() ? $this->get($className) : $this->expect($className);
        });
    }
}

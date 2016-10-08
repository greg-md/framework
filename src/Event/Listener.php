<?php

namespace Greg\Event;

use Greg\Support\Accessor\AccessorTrait;
use Greg\Support\IoC\IoCManagerAccessorTrait;
use Greg\Support\IoC\IoCManagerInterface;
use Greg\Support\Obj;
use Greg\Support\Str;

class Listener implements ListenerInterface
{
    use AccessorTrait, IoCManagerAccessorTrait;

    public function __construct(IoCManagerInterface $ioCManager = null)
    {
        if ($ioCManager) {
            $this->setIoCManager($ioCManager);
        }

        return $this;
    }

    public function on($eventName, callable $callable)
    {
        $this->accessor[$eventName][] = $callable;

        return $this;
    }

    public function register($eventName, $object)
    {
        if (!is_object($object)) {
            throw new \Exception('Event registrar should be an object.');
        }

        foreach ((array) $eventName as $eName) {
            $method = lcfirst(Str::phpName($eName));

            if (!method_exists($object, $method)) {
                throw new \Exception('Method `' . $method . '` not found in class `' . get_class($object) . '`.');
            }

            $this->on($eName, [$object, $method]);
        }

        return $this;
    }

    public function fire($eventName, ...$args)
    {
        return $this->fireRef($eventName, ...$args);
    }

    public function fireRef($eventName, &...$args)
    {
        return $this->fireArgs($eventName, $args);
    }

    public function fireArgs($eventName, array $args = [])
    {
        foreach ((array) $this->getFromAccessor($eventName) as $function) {
            if ($ioc = $this->getIoCManager()) {
                $ioc->callCallable($function, ...$args);
            } else {
                Obj::callCallable($function, ...$args);
            }
        }

        return $this;
    }

    public function fireWith($eventName, ...$args)
    {
        return $this->fireWithRef($eventName, ...$args);
    }

    public function fireWithRef($eventName, &...$args)
    {
        return $this->fireWithArgs($eventName, $args);
    }

    public function fireWithArgs($eventName, array $args = [])
    {
        foreach ((array) $this->getFromAccessor($eventName) as $function) {
            if ($ioc = $this->getIoCManager()) {
                $ioc->callCallableWith($function, ...$args);
            } else {
                Obj::callCallableWith($function, ...$args);
            }
        }

        return $this;
    }
}

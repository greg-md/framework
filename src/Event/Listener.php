<?php

namespace Greg\Event;

use Greg\Engine\InternalTrait;
use Greg\Storage\AccessorTrait;
use Greg\Tool\Arr;
use Greg\Tool\Str;

class Listener implements ListenerInterface
{
    use AccessorTrait, InternalTrait;

    public function on($eventName, callable $callable)
    {
        $this->storage[$eventName][] = $callable;

        return $this;
    }

    public function register($eventName, $object)
    {
        if (!is_object($object)) {
            throw new \Exception('Event registrar should be an object.');
        }

        foreach((array)$eventName as $eName) {
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
        foreach((array)$this->getFromStorage($eventName) as $function) {
            $this->callCallable($function, ...$args);
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
        foreach((array)$this->getFromStorage($eventName) as $function) {
            $this->callCallableWith($function, ...$args);
        }

        return $this;
    }

    public function subscribe(SubscriberInterface $subscriber)
    {
        $subscriber->subscribe($this);

        return $this;
    }
}
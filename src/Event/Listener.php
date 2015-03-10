<?php

namespace Greg\Event;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Arr;
use Greg\Support\Str;

class Listener implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

    public function on($event, $function, $id = null)
    {
        if ($id !== null) {
            $this[$event][$id] = $function;
        } else {
            $this[$event][] = $function;
        }

        return $this;
    }

    public function register($event, $class)
    {
        if (!is_object($class)) {
            throw Exception::create($this->appName(), 'Event registrar is not an object.');
        }

        foreach(Arr::bring($event) as $event) {
            $method = lcfirst(Str::phpName($event));

            $this->on($event, [$class, $method], get_class($class) . '::' . $method);
        }

        return $this;
    }

    public function fire($event, ...$args)
    {
        return $this->fireArgs($event, $args);
    }

    public function fireArgs($event, array $params = [])
    {
        $binder = $this->app()->binder();

        if (isset($this[$event])) foreach($this[$event] as $function) {
            $binder->callArgs($function, $params);
        }

        return $this;
    }

    public function addSubscribers($subscribers, \Closure $callback = null)
    {
        foreach($subscribers as $name => $subscriber) {
            $this->subscribe($name, $subscriber, $callback);
        }

        return $this;
    }

    public function subscribe($name, $subscriber, \Closure $callback = null)
    {
        if (is_string($subscriber)) {
            $subscriber = $this->app()->newInstance($subscriber);
        }

        if (!($subscriber instanceof SubscriberInterface)) {
            throw Exception::create($this->appName(), 'Subscriber `' . $name
                . '` should be an instance of Greg\Event\SubscriberInterface');
        }

        $subscriber->subscribe($this);

        if ($callback) {
            $callback($subscriber);
        }

        return $this;
    }
}
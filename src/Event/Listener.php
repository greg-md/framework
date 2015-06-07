<?php

namespace Greg\Event;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Support\Arr;
use Greg\Support\Str;

class Listener
{
    use Accessor, Internal;

    public function __construct(array $events = [], array $subscribers = [])
    {
        $this->addMore($events);

        $this->addSubscribers($subscribers);

        return $this;
    }

    static public function create($appName, array $events = [], array $subscribers = [])
    {
        return static::newInstanceRef($appName, $events, $subscribers);
    }

    public function addMore(array $events)
    {
        foreach($events as $key => $event) {
            $name = array_shift($event);

            if (!$name) {
                throw new \Exception('Event name is required in listener.');
            }

            $call = array_shift($event);

            if (!$call) {
                throw new \Exception('Event caller is required in listener.');
            }

            $this->on($name, $call, array_shift($event));
        }

        return $this;
    }

    public function on($event, callable $call, $id = null)
    {
        if ($id !== null) {
            $this->storage[$event][$id] = $call;
        } else {
            $this->storage[$event][] = $call;
        }

        return $this;
    }

    public function register($event, $class)
    {
        if (!is_object($class)) {
            throw new \Exception('Event registrar should be an object.');
        }

        foreach(Arr::bring($event) as $event) {
            $method = lcfirst(Str::phpName($event));

            if (!method_exists($class, $method)) {
                throw new \Exception('Method `' . $method . '` not found in class `' . get_class($class) . '`.');
            }

            $this->on($event, [$class, $method], get_class($class) . '::' . $method);
        }

        return $this;
    }

    public function fire($event, ...$args)
    {
        return $this->fireRef($event, ...$args);
    }

    public function fireRef($event, &...$args)
    {
        return $this->fireArgs($event, $args);
    }

    public function fireArgs($event, array $args = [])
    {
        $binder = $this->app()->binder();

        if (Arr::has($this->storage, $event)) {
            foreach($this->storage[$event] as $function) {
                $binder->callArgs($function, $args);
            }
        }

        return $this;
    }

    public function addSubscribers($subscribers, callable $callback = null)
    {
        foreach($subscribers as $name => $subscriber) {
            $this->subscribe($name, $subscriber, $callback);
        }

        return $this;
    }

    public function subscribe($name, $subscriber, callable $callback = null)
    {
        if (is_string($subscriber)) {
            $subscriber = $this->app()->loadInstance($subscriber);
        }

        if (!($subscriber instanceof SubscriberInterface)) {
            throw new \Exception('Subscriber `' . $name . '` should be an instance of Greg\Event\SubscriberInterface.');
        }

        $subscriber->subscribe($this);

        if ($callback) {
            $this->app()->binder()->call($callback, $subscriber);
        }

        return $this;
    }
}
<?php

namespace Greg\Event;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Support\Arr;
use Greg\Support\Str;

class Listener
{
    use Accessor, Internal;

    public function __construct(array $events = [])
    {
        $this->addMore($events);

        return $this;
    }

    static public function create($appName, array $events = [])
    {
        return static::newInstanceRef($appName, $events);
    }

    public function addMore(array $events)
    {
        foreach($events as $key => $event) {
            $name = array_shift($event);

            if (!$name) {
                throw Exception::newInstance($this->appName(), 'Event name is required in listener.');
            }

            $call = array_shift($event);

            if (!$call) {
                throw Exception::newInstance($this->appName(), 'Event caller is required in listener.');
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
            throw Exception::newInstance($this->appName(), 'Event registrar should be an object.');
        }

        foreach(Arr::bring($event) as $event) {
            $method = lcfirst(Str::phpName($event));

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
            throw Exception::newInstance($this->appName(), 'Subscriber `' . $name
                . '` should be an instance of Greg\Event\SubscriberInterface');
        }

        $subscriber->subscribe($this);

        if ($callback) {
            call_user_func_array($callback, [&$subscriber]);
        }

        return $this;
    }
}
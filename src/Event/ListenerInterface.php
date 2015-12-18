<?php

namespace Greg\Event;

interface ListenerInterface
{
    public function addMore(array $events);

    public function on($event, callable $call, $id = null);

    public function register($event, $class);

    public function fire($event, ...$args);

    public function fireRef($event, &...$args);

    public function fireArgs($event, array $args = []);

    public function fireWith($event, ...$args);

    public function fireWithRef($event, &...$args);

    public function fireWithArgs($event, array $args = []);

    public function addSubscribers($subscribers, callable $callback = null);

    public function subscribe($name, $subscriber, callable $callback = null);
}
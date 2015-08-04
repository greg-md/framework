<?php

namespace Greg\Support\Event;

interface SubscriberInterface
{
    public function subscribe(ListenerInterface $listener);

    public function fire($event, ...$args);

    public function fireRef($event, &...$args);

    public function fireArgs($event, array $args = []);

    public function fireWith($event, ...$args);

    public function fireWithRef($event, &...$args);

    public function fireWithArgs($event, array $args = []);
}
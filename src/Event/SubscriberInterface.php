<?php

namespace Greg\Event;

interface SubscriberInterface
{
    public function subscribe(Listener $listener);

    public function fire($event, ...$args);

    public function fireRef($event, &...$args);

    public function fireArgs($event, array $args = []);
}
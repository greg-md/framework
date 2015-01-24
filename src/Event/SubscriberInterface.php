<?php

namespace Greg\Event;

interface SubscriberInterface
{
    public function subscribe(Listener $listener);

    public function fire($event, $_ = null);

    public function fireArgs($event, array $param = []);
}
<?php

namespace Greg\Event;

interface SubscriberInterface
{
    public function subscribe(ListenerInterface $listener);
}

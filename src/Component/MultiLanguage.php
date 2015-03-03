<?php

namespace Greg\Component;

use Greg\Application\Runner;
use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Event\Listener;
use Greg\Event\SubscriberInterface;
use Greg\Event\SubscriberTrait;

class MultiLanguage implements SubscriberInterface, InternalInterface
{
    use SubscriberTrait, Internal;

    public function subscribe(Listener $listener)
    {
        $listener->register([
            Runner::EVENT_STARTUP,
        ], $this);

        return $this;
    }
}
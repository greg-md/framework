<?php

namespace Greg\Event;

use Greg\Engine\InternalTrait;

class Listener extends \Greg\Support\Event\Listener
{
    use InternalTrait;

    static public function create($appName, array $events = [], array $subscribers = [])
    {
        return static::newInstanceRef($appName, $events, $subscribers);
    }
}
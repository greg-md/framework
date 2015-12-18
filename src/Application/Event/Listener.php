<?php

namespace Greg\Application\Event;

use Greg\Application\Engine\InternalTrait;

class Listener extends \Greg\Event\Listener
{
    use InternalTrait;

    static public function create($appName, array $events = [], array $subscribers = [])
    {
        return static::newInstanceRef($appName, $events, $subscribers);
    }
}
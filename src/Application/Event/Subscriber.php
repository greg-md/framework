<?php

namespace Greg\Application\Event;

use Greg\Application\Engine\InternalTrait;

abstract class Subscriber extends \Greg\Event\Subscriber
{
    use InternalTrait;
}
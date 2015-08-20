<?php

namespace Greg\Event;

use Greg\Engine\InternalTrait;

abstract class Subscriber extends \Greg\Support\Event\Subscriber
{
    use InternalTrait;
}
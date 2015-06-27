<?php

namespace Greg\Event;

use Greg\Support\Engine\InternalTrait;

abstract class Subscriber implements SubscriberInterface
{
    use SubscriberTrait, InternalTrait;
}
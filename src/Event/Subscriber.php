<?php

namespace Greg\Event;

use Greg\Engine\InternalTrait;

abstract class Subscriber implements SubscriberInterface
{
    use SubscriberTrait, InternalTrait;
}
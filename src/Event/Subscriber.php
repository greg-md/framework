<?php

namespace Greg\Event;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;

abstract class Subscriber implements SubscriberInterface, InternalInterface
{
    use SubscriberTrait, Internal;
}
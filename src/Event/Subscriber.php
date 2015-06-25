<?php

namespace Greg\Event;

use Greg\Support\Engine\Internal;

abstract class Subscriber implements SubscriberInterface
{
    use SubscriberTrait, Internal;
}
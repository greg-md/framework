<?php

namespace Greg\Event;

use Greg\Engine\Internal;

abstract class Subscriber implements SubscriberInterface
{
    use SubscriberTrait, Internal;
}
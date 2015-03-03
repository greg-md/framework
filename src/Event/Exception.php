<?php

namespace Greg\Event;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;

class Exception extends \Exception implements InternalInterface
{
    use Internal;
}
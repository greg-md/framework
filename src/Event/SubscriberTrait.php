<?php

namespace Greg\Event;

use Greg\Support\Str;

trait SubscriberTrait
{
    abstract public function subscribe(Listener $listener);

    public function fire($event, $_ = null)
    {
        $args = func_get_args();

        array_shift($args);

        return $this->fireArgs($event, $args);
    }

    public function fireArgs($event, array $param = [])
    {
        $method = lcfirst(Str::phpName($event));

        if (method_exists($this, $method)) {
            call_user_func_array([$this, $method], $param);
        }

        return $this;
    }
}
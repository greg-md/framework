<?php

namespace Greg\Event;

use Greg\Support\Str;

trait SubscriberTrait
{
    public function fire($event, ...$args)
    {
        return $this->fireRef($event, ...$args);
    }

    public function fireRef($event, &...$args)
    {
        return $this->fireArgs($event, $args);
    }

    public function fireArgs($event, array $args = [])
    {
        $method = lcfirst(Str::phpName($event));

        if (method_exists($this, $method)) {
            $this->callCallable([$this, $method], ...$args);
        }

        return $this;
    }

    public function fireWith($event, ...$args)
    {
        return $this->fireWithRef($event, ...$args);
    }

    public function fireWithRef($event, &...$args)
    {
        return $this->fireWithArgs($event, $args);
    }

    public function fireWithArgs($event, array $args = [])
    {
        $method = lcfirst(Str::phpName($event));

        if (method_exists($this, $method)) {
            $this->callCallableWith([$this, $method], ...$args);
        }

        return $this;
    }
}

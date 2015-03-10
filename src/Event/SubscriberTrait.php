<?php

namespace Greg\Event;

use Greg\Application\Runner;
use Greg\Support\Str;

trait SubscriberTrait
{
    abstract public function subscribe(Listener $listener);

    public function fire($event, ...$args)
    {
        return $this->fireArgs($event, $args);
    }

    public function fireArgs($event, array $param = [])
    {
        $method = lcfirst(Str::phpName($event));

        if (method_exists($this, $method)) {
            $this->app()->binder()->callArgs([$this, $method], $param);
        }

        return $this;
    }

    /**
     * @param Runner $app
     * @return Runner
     */
    abstract public function app(Runner $app = null);
}
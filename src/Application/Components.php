<?php

namespace Greg\Application;

use Greg\Engine\Internal;
use Greg\Event\SubscriberInterface;

class Components
{
    use Internal;

    protected $storage = [];

    public function addMore(array $resources, callable $callable = null)
    {
        foreach($resources as $name => $component) {
            $this->add($name, $component, $callable);
        }

        return $this;
    }

    public function add($name, $component, callable $callable = null)
    {
        if (is_string($component)) {
            $component = $this->app()->newInstance($component);
        }

        $this->memory('component/' . $name, $component);

        $this->app()->binder()->add($component);

        if ($component instanceof SubscriberInterface) {
            $this->app()->listener()->subscribe('component/' . $name, $component);
        }

        if ($callable) {
            call_user_func_array($callable, [$component]);
        }

        return $this;
    }

    public function has($name)
    {
        return $this->memory('component/' . $name) ? true : false;
    }

    public function get($name)
    {
        $component = $this->memory('component/' . $name);

        if (!$component) {
            throw Exception::create($this->appName(), 'Undefined component `' . $name . '`.');
        }

        return $component;
    }
}
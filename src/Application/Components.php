<?php

namespace Greg\Application;

use Greg\Engine\InternalTrait;
use Greg\Event\SubscriberInterface;

class Components
{
    use InternalTrait;

    public function __construct(array $components = [])
    {
        $this->addMore($components);

        return $this;
    }

    static public function create($appName, array $components = [])
    {
        return static::newInstanceRef($appName, $components);
    }

    public function addMore(array $components, callable $callback = null)
    {
        foreach($components as $name => $component) {
            $this->add($name, $component, $callback);
        }

        return $this;
    }

    public function add($name, $component, callable $callback = null)
    {
        if (is_string($component)) {
            $component = $this->app()->loadInstance($component);
        } elseif (($component instanceof \Closure) or is_array($component)) {
            $component = $this->app()->binder()->call($component);
        }

        $this->memory('component/' . $name, $component);

        $this->app()->binder()->setObject($component);

        if ($component instanceof SubscriberInterface) {
            $this->app()->listener()->subscribe('component/' . $name, $component);
        }

        if ($callback) {
            $this->app()->binder()->call($callback, $component);
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
            throw new \Exception('Undefined component `' . $name . '`.');
        }

        return $component;
    }
}
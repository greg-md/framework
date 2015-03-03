<?php

namespace Greg\Application;

use Greg\Engine\Internal;

class Resources
{
    use Internal;

    protected $storage = [];

    public function addMore(array $resources)
    {
        foreach($resources as $name => $class) {
            $this->add($name, $class);
        }

        return $this;
    }

    public function add($name, $class)
    {
        $this->storage[$name] = (array)$class;

        return $this;
    }

    public function getClasses()
    {
        $classNames = [];

        foreach($this->storage as $name => $class) {
            $classNames[$name] = $class[0];
        }

        return $classNames;
    }

    public function get($name)
    {
        $resource = $this->memory('resource/' . $name);

        if (!$resource) {
            if (!isset($this->storage[$name])) {
                throw Exception::create($this->appName(), 'Undefined resource `' . $name . '`.');
            }

            $resource = call_user_func_array([$this->app(), 'newClass'], $this->storage[$name]);

            $this->memory('resource/' . $name, $resource);
        }

        return $resource;
    }
}
<?php

namespace Greg\Html;

use Greg\Engine\InternalTrait;
use Greg\Support\Storage\AccessorTrait;

class ElementClass
{
    use AccessorTrait, InternalTrait;

    public function add($class, $id = null)
    {
        if (is_array($class)) {
            foreach(($classes = $class) as $class) {
                $this->storage[$class] = $id;
            }
        } else {
            $this->storage[$class] = $id;
        }

        return $this;
    }

    public function delete($class)
    {
        unset($this->storage[$class]);

        return $this;
    }

    public function __toString()
    {
        return implode(' ', array_keys($this->storage));
    }
}
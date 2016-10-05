<?php

namespace Greg\Html;

use Greg\Support\Accessor\AccessorTrait;

class HtmlElementClass
{
    use AccessorTrait;

    public function add($class, $id = null)
    {
        if (is_array($class)) {
            foreach (($classes = $class) as $class) {
                $this->accessor[$class] = $id;
            }
        } else {
            $this->accessor[$class] = $id;
        }

        return $this;
    }

    public function delete($class)
    {
        unset($this->accessor[$class]);

        return $this;
    }

    public function __toString()
    {
        return implode(' ', array_keys($this->accessor));
    }
}

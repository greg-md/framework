<?php

namespace Greg\Storage;

use Greg\Support\Obj;

trait IteratorAggregate
{
    protected $iteratorClass = 'ArrayIterator';

    public function getIterator()
    {
        $class = $this->iteratorClass();

        if (!$class) {
            throw new \Exception('Undefined ArrayObject iterator.');
        }

        return new $class($this);
    }

    public function iteratorClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
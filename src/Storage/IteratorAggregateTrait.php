<?php

namespace Greg\Storage;

use Greg\Tool\Obj;

trait IteratorAggregateTrait
{
    protected $iteratorClass = \ArrayIterator::class;

    public function getIterator()
    {
        $class = $this->iteratorClass();

        if (!$class) {
            throw new \Exception('Undefined iterator.');
        }

        return new $class($this->getIteratorAccessor());
    }

    public function iteratorClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    abstract protected function getIteratorAccessor();
}
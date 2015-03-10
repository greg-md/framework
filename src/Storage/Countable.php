<?php

namespace Greg\Storage;

trait Countable
{
    abstract protected function &accessor(array $accessor = []);

    public function count()
    {
        return sizeof($this->accessor());
    }
}
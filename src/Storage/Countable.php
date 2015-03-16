<?php

namespace Greg\Storage;

trait Countable
{
    abstract protected function &accessor(array $storage = []);

    public function count()
    {
        return sizeof($this->accessor());
    }
}
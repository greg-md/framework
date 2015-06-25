<?php

namespace Greg\Support\Storage;

trait Countable
{
    abstract protected function &accessor(array $storage = []);

    public function count()
    {
        return sizeof($this->accessor());
    }
}
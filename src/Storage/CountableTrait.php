<?php

namespace Greg\Storage;

trait CountableTrait
{
    abstract protected function &accessor(array $storage = []);

    public function count()
    {
        return sizeof($this->accessor());
    }
}
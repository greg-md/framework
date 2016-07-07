<?php

namespace Greg\Storage;

trait CountableTrait
{
    public function count()
    {
        return sizeof($this->storage);
    }
}
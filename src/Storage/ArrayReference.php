<?php

namespace Greg\Storage;

class ArrayReference
{
    protected $var = null;

    public function __construct(&$var)
    {
        $this->var = &$var;
    }

    public function &get()
    {
        return $this->var;
    }
}
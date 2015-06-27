<?php

namespace Greg\Support\System;

use Greg\Support\Obj;

class File
{
    protected $file = null;

    public function __construct($file)
    {
        $this->file($file);

        return $this;
    }

    public function ext()
    {
        return \Greg\Support\File::ext($this->file());
    }

    public function mime()
    {
        return \Greg\Support\File::mime($this->file());
    }

    public function file($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
<?php

namespace Greg\System;

use Greg\Support\Engine\Internal;
use Greg\Support\Obj;

class File
{
    use Internal;

    protected $file = null;

    public function __construct($file)
    {
        $this->file($file);

        return $this;
    }

    static public function create($appName, $file)
    {
        return static::newInstanceRef($appName, $file);
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
<?php

namespace Greg\Resource;

use Greg\Engine\Internal;
use Greg\Support\Obj;
use \Greg\Support\File as FileHelper;

class File
{
    use Internal;

    protected $file = null;

    public function __construct($file)
    {
        $this->file($file);

        return $this;
    }

    public function ext()
    {
        return FileHelper::ext($this->file());
    }

    public function mime()
    {
        return FileHelper::mime($this->file());
    }

    public function file($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

}
<?php

namespace Greg\Support;

class File
{
    protected $file = null;

    public function __construct($file)
    {
        $this->file($file);

        return $this;
    }

    public function ext($point = false)
    {
        return $this->extFile($this->file(), $point);
    }

    public function mime()
    {
        return $this->mimeFile($this->file());
    }

    static public function extFile($file, $point = false)
    {
        $file = explode('.', $file);

        return ($point ? '.' : '') . (sizeof($file > 1) ? end($file) : null);
    }

    static public function mimeFile($file)
    {
        return (new \finfo)->file($file, FILEINFO_MIME_TYPE);
    }

    static public function fixFileDir($file, $recursive = false)
    {
        return Dir::fix(dirname($file), $recursive);
    }

    public function file($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
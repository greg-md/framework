<?php

namespace Greg\Support\Config;

use Greg\Support\Tool\Obj;

class ConfigIniFile extends ConfigIni
{
    protected $file = null;

    public function __construct($file = null, $section = null, $indexDelimiter = null)
    {
        if ($file) {
            $this->file($file);
        }
        $file = $this->file();

        return parent::__construct($file ? parse_ini_file($file, true) : null, $section, $indexDelimiter);
    }

    static public function fetch($file, $section = null, $indexDelimiter = false)
    {
        return parent::fetchContents(parse_ini_file($file, true), $section, $indexDelimiter);
    }

    public function file($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
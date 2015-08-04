<?php

namespace Greg\Support\Config;

class ConfigIniString extends ConfigIni
{
    public function __construct($string = null, $section = null, $indexDelimiter = null)
    {
        return parent::__construct($string ? parse_ini_string($string, true) : null, $section, $indexDelimiter);
    }

    static public function fetch($string, $section = null, $indexDelimiter = false)
    {
        return parent::fetchContents(parse_ini_string($string, true), $section, $indexDelimiter);
    }
}
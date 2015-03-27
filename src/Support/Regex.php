<?php

namespace Greg\Support;

use Greg\Support\Regex\InNamespace;

class Regex
{
    const PATTERN = '#';

    static public function inNamespace($start, $end, $recursive = true)
    {
        return new InNamespace($start, $end, $recursive);
    }

    static public function disableGroups($regex)
    {
        return preg_replace(static::pattern('(?<!\\\\)\((?!\?)'), '(?:', $regex);
    }

    static public function quote($string, $delimiter = self::PATTERN)
    {
        return preg_quote($string, $delimiter);
    }

    static public function pattern($regex, $flags = null)
    {
        return static::PATTERN . $regex . static::PATTERN . $flags;
    }
}
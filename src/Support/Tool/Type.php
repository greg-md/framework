<?php

namespace Greg\Support\Tool;

class Type
{
    static public function isNaturalNumber($var)
    {
        return ctype_digit((string)$var);
    }
}
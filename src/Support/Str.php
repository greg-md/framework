<?php

namespace Greg\Support;

class Str
{
    const CAMEL_CASE = 'camelCase';

    static public function camelCase($var)
    {
        $keywords = preg_replace('#[^a-z0-9]+#i', ' ', $var);

        $keywords = ucwords($keywords);

        $keywords = str_replace(' ', '', $keywords);

        return $keywords;
    }

    static public function trainCase($var)
    {
        $var = preg_replace(['#[^a-z0-9]+#i', '#([a-z]+)([A-Z]+)#'], [' ', '$1 $2'], $var);

        $var = trim($var);

        $var = mb_strtolower($var);

        $var = ucwords($var);

        $var = str_replace(' ', '-', $var);

        return $var;
    }

    static public function spinalCase($var)
    {
        return mb_strtolower(static::trainCase($var));
    }

    static public function phpName($var, $type = self::CAMEL_CASE)
    {
        $var = static::$type($var);

        if (!$var or Type::isNaturalNumber($var[0])) {
            $var = '_' . $var;
        }

        return $var;
    }

    static public function replaceAccents($str)
    {
        $search = explode(',', 'ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,ø,Ø,Å,Á,À,Â,Ä,È,É,Ê,Ë,Í,Î,Ï,Ì,Ò,Ó,Ô,Ö,Ú,Ù,Û,Ü,Ÿ,Ç,Æ,Œ');

        $replace = explode(',', 'c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,o,O,A,A,A,A,A,E,E,E,E,I,I,I,I,O,O,O,O,U,U,U,U,Y,C,AE,OE');

        return str_replace($search, $replace, $str);
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param  string  $pattern
     * @param  string  $value
     * @return bool
     */
    static public function is($pattern, $value)
    {
        if ($pattern == $value) return true;

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool)preg_match('#^' . $pattern . '$#', $value);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }

        return false;
    }

    static public function quote($str, $with = '"')
    {
        return $with . $str . $with;
    }
}
<?php

namespace Greg\Support;

class Str
{
    const SPLIT_CASE = 'splitCase';

    const SPLIT_UPPER_CASE = 'splitUpperCase';

    const CAMEL_CASE = 'camelCase';

    const TRAIN_CASE = 'trainCase';

    const SPINAL_CASE = 'spinalCase';

    static public function splitCase($var, $delimiter = ' ')
    {
        $var = preg_replace('#[^a-z0-9]+#i', $delimiter, $var);

        $var = trim($var);

        return $var;
    }

    static public function splitUpperCase($var, $delimiter = ' ')
    {
        $var = static::splitCase($var, $delimiter);

        $var = preg_replace('#([a-z]+)([A-Z]+)#', '$1' . $delimiter . '$2', $var);

        return $var;
    }

    static public function camelCase($var)
    {
        $var = static::splitCase($var);

        $var = ucwords($var);

        $var = str_replace(' ', '', $var);

        return $var;
    }

    static public function trainCase($var)
    {
        $var = static::splitUpperCase($var);

        $var = ucwords($var);

        $var = str_replace(' ', '-', $var);

        return $var;
    }

    static public function spinalCase($var)
    {
        return static::splitUpperCase($var, '-');
    }

    static public function phpName($var, $type = self::CAMEL_CASE)
    {
        $var = static::$type($var);

        if (!$var or Type::isNaturalNumber($var[0])) {
            $var = '_' . $var;
        }

        return $var;
    }

    static public function abbreviation($var)
    {
        $var = static::splitUpperCase($var);

        $var = ucwords($var);

        $var = explode(' ', $var);

        $var = array_map(function($var) {
            return $var[0];
        }, $var);

        $var = implode('', $var);

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
        Arr::bringRef($needles);

        foreach ($needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) return true;
        }

        return false;
    }

    static public function quote($str, $with = '"')
    {
        return $with . $str . $with;
    }

    static public function splitPath($string, $delimiter = '/', $limit = null)
    {
        $string = trim($string, $delimiter);

        return static::split($string, $delimiter, $limit);
    }

    static public function split($string, $delimiter = '', $limit = null)
    {
        if (static::isEmpty($string)) {
            return [];
        }

        $args = [$delimiter, $string];

        if ($limit !== null) {
            $args[] = $limit;
        }

        return explode(...$args);
    }

    static public function splitQuoted($string, $delimiter = ',', $quotes = '"')
    {
        $string = static::split($string, $delimiter);

        $string = array_map(function($column) use ($quotes) {
            return trim($column, $quotes);
        }, $string);

        return $string;
    }

    static public function isEmpty($string)
    {
        return $string === null or $string === '';
    }

    static public function parse($string, $delimiter = '&', $keyValueDelimiter = '=')
    {
        if ($delimiter === '&' and $keyValueDelimiter === '=') {
            parse_str($string, $output);

            return $output;
        }

        $output = [];

        foreach(explode($delimiter, $string) as $part) {
            list($key, $value) = explode($keyValueDelimiter, $part);

            $output[$key] = $value;
        }

        return $output;
    }
}
<?php

namespace Greg\Server;

class Ini
{
    static public function all($extension = null, $details = true)
    {
        return ini_get_all(...func_get_args());
    }

    static public function param($key = null, $value = null)
    {
        $args = func_get_args();

        if ($args) {
            $var = array_shift($args);

            if (is_array($var)) {
                foreach($var as $key => $value) {
                    static::set($key, $value);
                }

                return true;
            }

            if ($args) {
                return static::set($var, array_shift($args));
            }

            return static::get($var);
        }

        return static::all();
    }

    static public function get($var)
    {
        if (($value = ini_get($var)) === false) {
            throw new \Exception('Server option `' . $var . '` does not exist.');
        }

        return $value;
    }

    static public function set($var, $value)
    {
        if (($oldValue = ini_set($var, $value)) === false) {
            throw new \Exception('Server option `' . $var . '` can not be set.');
        }

        return $oldValue;
    }
}
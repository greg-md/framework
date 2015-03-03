<?php

namespace Greg\Server;

use Greg\Support\Arr;

class Session
{
    static protected $persistent = false;

    static protected $handler = null;

    static public function ini($var, $value = null)
    {
        if (is_array($var)) {
            foreach (($param = $var) as $var => $value) {
                static::iniSet($var, $value);
            }

            return true;
        }

        if (func_num_args() > 1) {
            return static::iniSet($var, $value);
        }

        return static::iniGet($var);
    }

    static public function iniSet($var, $value)
    {
        return Ini::set('session.' . $var, $value);
    }

    static public function iniGet($var)
    {
        return Ini::get('session.' . $var);
    }

    static public function id($id = null)
    {
        return func_num_args() ? session_id($id) : session_id();
    }

    static public function getId()
    {
        static::start();

        return static::id();
    }

    static public function persistent($value = null)
    {
        return func_num_args() ? (static::$persistent = $value) : static::$persistent;
    }

    static public function name($name = null)
    {
        return func_num_args() ? session_name($name) : session_name();
    }

    static public function start()
    {
        if (!isset($_SESSION)) {
            session_start();

            if (static::persistent()) {
                static::resetLifetime();
            }
        }

        return true;
    }

    static public function unserialize($data)
    {
        return static::unserializePart($data);
    }

    static protected function unserializePart($data, $startIndex = 0, &$dict = null)
    {
        isset($dict) or $dict = [];

        $nameEnd = strpos($data, '|', $startIndex);

        if ($nameEnd !== false) {
            $name = substr($data, $startIndex, $nameEnd - $startIndex);

            $rest = substr($data, $nameEnd + 1);

            $value = unserialize($rest); // PHP will unserialize up to "|" delimiter.

            $dict[$name] = $value;

            return static::unserializePart($data, $nameEnd + 1 + strlen(serialize($value)), $dict);
        }

        return $dict;
    }

    static public function decode($data, $return = false)
    {
        return $return ? static::unserialize($data) : session_decode($data);
    }

    static public function resetLifetime($time = null, $path = null, $domain = null, $secure = null, $httpOnly = null)
    {
        if ($time === null) {
            $time = ini_get('session.cookie_lifetime');
        }

        if ($path === null) {
            $path = ini_get('session.cookie_path');
        }

        if ($domain === null) {
            $domain = ini_get('session.cookie_domain');
        }

        if ($secure === null) {
            $secure = ini_get('session.cookie_secure');
        }

        if ($httpOnly === null) {
            $httpOnly = ini_get('session.cookie_httponly');
        }

        if ($time > 0) {
            $time += time();
        }

        setcookie(static::name(), static::getId(), $time, $path, $domain, $secure, $httpOnly);

        return true;
    }

    static public function saveHandler($handler = null)
    {
        if ($handler !== null) {
            session_set_save_handler($handler);

            static::$handler = $handler;
        }

        return static::$handler;
    }

    static public function has($index)
    {
        static::start();

        return array_key_exists($index, $_SESSION);
    }

    static public function indexHas($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::indexHas($_SESSION, $index, $delimiter);
    }

    static public function &get($index, $else = null)
    {
        static::start();

        if (static::has($index)) return $_SESSION[$index]; return $else;
    }

    static public function &indexGet($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::indexGet($_SESSION, $index, $else, $delimiter);
    }

    static public function set($index, $value)
    {
        static::start();

        $_SESSION[$index] = $value;

        return true;
    }

    static public function indexSet($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::indexSet($_SESSION, $index, $value, $delimiter);
    }

    static public function del($index)
    {
        static::start();

        $_SESSION[$index] = null;

        unset($_SESSION[$index]);

        return true;
    }

    static public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::indexDel($_SESSION, $index, $delimiter);
    }

    static public function destroy()
    {
        static::start();

        session_destroy();

        return true;
    }

    static public function &storage()
    {
        static::start();

        return $_SESSION;
    }
}
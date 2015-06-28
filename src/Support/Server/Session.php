<?php

namespace Greg\Support\Server;

use Greg\Support\Arr;
use Greg\Support\Obj;

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
        return session_id(...func_get_args());
    }

    static public function getId()
    {
        static::start();

        return static::id();
    }

    static public function persistent($value = null)
    {
        return Obj::fetchBoolVar(true, static::$persistent, ...func_get_args());
    }

    static public function name($name = null)
    {
        return session_name(...func_get_args());
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

    // Start standard array methods

    static public function &all()
    {
        static::start();

        return $_SESSION;
    }

    static public function has($key, ...$keys)
    {
        static::start();

        return Arr::has($_SESSION, $key, ...$keys);
    }

    static public function hasIndex($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::hasIndex($_SESSION, $index, $delimiter);
    }

    static public function set($key, $value)
    {
        static::start();

        return Arr::set($_SESSION, $key, $value);
    }

    static public function setRef($key, &$value)
    {
        static::start();

        return Arr::set($_SESSION, $key, $value);
    }

    static public function setIndex($index, $value, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::setIndex($_SESSION, $index, $value, $delimiter);
    }

    static public function setIndexRef($index, &$value, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::setIndex($_SESSION, $index, $value, $delimiter);
    }

    static public function get($key, $else = null)
    {
        static::start();

        return Arr::get($_SESSION, $key, $else);
    }

    static public function &getRef($key, $else = null)
    {
        static::start();

        return Arr::getRef($_SESSION, $key, $else);
    }

    static public function getForce($key, $else = null)
    {
        static::start();

        return Arr::getForce($_SESSION, $key, $else);
    }

    static public function &getForceRef($key, $else = null)
    {
        static::start();

        return Arr::getForceRef($_SESSION, $key, $else);
    }

    static public function getArray($key, $else = null)
    {
        static::start();

        return Arr::getArray($_SESSION, $key, $else);
    }

    static public function &getArrayRef($key, $else = null)
    {
        static::start();

        return Arr::getArrayRef($_SESSION, $key, $else);
    }

    static public function getArrayForce($key, $else = null)
    {
        static::start();

        return Arr::getArrayForce($_SESSION, $key, $else);
    }

    static public function &getArrayForceRef($key, $else = null)
    {
        static::start();

        return Arr::getArrayForceRef($_SESSION, $key, $else);
    }

    static public function getIndex($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::getIndex($_SESSION, $index, $else, $delimiter);
    }

    static public function &getIndexRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::getIndexRef($_SESSION, $index, $else, $delimiter);
    }

    static public function getIndexForce($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::getIndexForce($_SESSION, $index, $else, $delimiter);
    }

    static public function &getIndexForceRef($index, $else = null, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::getIndexForceRef($_SESSION, $index, $else, $delimiter);
    }

    static public function getIndexArray($index, $else = null)
    {
        static::start();

        return Arr::getIndexArray($_SESSION, $index, $else);
    }

    static public function &getIndexArrayRef($index, $else = null)
    {
        static::start();

        return Arr::getIndexArrayRef($_SESSION, $index, $else);
    }

    static public function getIndexArrayForce($index, $else = null)
    {
        static::start();

        return Arr::getIndexArrayForce($_SESSION, $index, $else);
    }

    static public function &getIndexArrayForceRef($index, $else = null)
    {
        static::start();

        return Arr::getIndexArrayForceRef($_SESSION, $index, $else);
    }

    static public function required($key)
    {
        static::start();

        return Arr::required($_SESSION, $key);
    }

    static public function &requiredRef($key)
    {
        static::start();

        return Arr::requiredRef($_SESSION, $key);
    }

    static public function del($key, ...$keys)
    {
        static::start();

        return Arr::del($_SESSION, $key, ...$keys);
    }

    static public function indexDel($index, $delimiter = Arr::INDEX_DELIMITER)
    {
        static::start();

        return Arr::delIndex($_SESSION, $index, $delimiter);
    }

    // End standard array methods

    static public function destroy()
    {
        static::start();

        session_destroy();

        return true;
    }
}
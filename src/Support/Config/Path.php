<?php

namespace Greg\Support\Config;

class Path
{
    static public function fetch($path, $env = null, $ext = '.php')
    {
        $config = static::fetchCurrent($path, $ext);

        if ($env) {
            foreach(static::fetchCurrent($path . DIRECTORY_SEPARATOR . $env, $ext) as $key => $conf) {
                $config[$key] = array_key_exists($key, $config) ? array_merge($config[$key], $conf) : $conf;
            }
        }

        return $config;
    }

    static protected function fetchCurrent($path, $ext = '.php')
    {
        $config = [];

        $extLen = mb_strlen($ext);

        foreach(glob($path . DIRECTORY_SEPARATOR . '*' . $ext) as $file) {
            if (is_file($file)) {
                $basename = basename($file);

                $basename = mb_substr($basename, 0, mb_strlen($basename) - $extLen);

                $config[$basename] = requireFile($file);
            }
        }

        return $config;
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 */
if (!function_exists('requireFile')) {
    function requireFile($file)
    {
        return require $file;
    }
}

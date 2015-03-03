<?php

namespace Greg\Config;

class Path
{
    static public function fetch($path, $env = null, $ext = '.php')
    {
        $config = static::fetchCurrent($path, $ext);

        if ($env) {
            foreach(static::fetchCurrent($path . DIRECTORY_SEPARATOR . $env, $ext) as $key => $config) {
                $config[$key] = isset($config[$key]) ? array_merge($config[$key], $config) : $config;
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

                $config[$basename] = require $file;
            }
        }

        return $config;
    }
}
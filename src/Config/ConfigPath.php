<?php

namespace Greg\Config;

class ConfigPath
{
    static public function fetch($path, array $params = [], $ext = '.php')
    {
        $config = [];

        $extLen = mb_strlen($ext);

        foreach(glob($path . DIRECTORY_SEPARATOR . '*' . $ext) as $file) {
            if (is_file($file)) {
                $basename = basename($file);

                $basename = mb_substr($basename, 0, mb_strlen($basename) - $extLen);

                $config[$basename] = ___gregRequireFile($file, $params);
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
if (!function_exists('___gregRequireFile')) {
    function ___gregRequireFile($___file, array $___params = [])
    {
        extract($___params);

        return require $___file;
    }
}

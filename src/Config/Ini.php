<?php

namespace Greg\Config;

use Greg\Engine\Internal;
use Greg\Event\Exception;
use Greg\Storage\ArrayIndexAccess;
use Greg\Support\Arr;
use Greg\Support\Obj;

class Ini
{
    use ArrayIndexAccess, Internal;

    protected $contents = null;

    protected $section = null;

    protected $indexDelimiter = null;

    public function __construct($contents, $section = null, $indexDelimiter = null)
    {
        if ($contents !== null) {
            $this->contents($contents);
        }

        if ($section !== null) {
            $this->section($section);
        }

        if ($indexDelimiter !== null) {
            $this->indexDelimiter($indexDelimiter);
        }

        $this->replace(static::fetchContents($contents, $section, $indexDelimiter));

        return $this;
    }

    static protected function fetchContents($contents, $section = null, $indexDelimiter = null)
    {
        $return = [];
        if ($contents) {
            if ($section) {
                $partsParam = [];
                foreach ($contents as $key => $value) {
                    $parts = array_map('trim', explode(':', $key));
                    $partsParam[$key] = $parts;
                    $primary = array_shift($parts);
                    $return[$primary] = $indexDelimiter ? static::fetchIndexes($value, $indexDelimiter) : $value;
                }

                foreach ($partsParam as $parts) {
                    $primary = array_shift($parts);
                    foreach ($parts as $part) {
                        $return[$primary] = array_replace_recursive($return[$part], $return[$primary]);
                    }
                }
            } else {
                $return = $indexDelimiter ? static::fetchIndexes($contents, $indexDelimiter) : $contents;
            }
            if ($section) {
                if (!isset($return[$section])) {
                    throw new Exception('Config ini section `' . $section . '` not found.');
                }
                $return = $return[$section];
            }
        }

        return $return;
    }

    static protected function fetchIndexes($contents, $indexDelimiter = Arr::INDEX_DELIMITER)
    {
        $fetchedSection = [];
        foreach ($contents as $key => $value) {
            $keys = explode($indexDelimiter, $key);
            $contentsLevel = &$fetchedSection;
            foreach ($keys as $part) {
                if ($part == '') {
                    $contentsLevel = &$contentsLevel[];
                } else {
                    $contentsLevel = &$contentsLevel[$part];
                }
            }
            if (is_array($value)) {
                $value = static::fetchIndexes($value, $indexDelimiter);
            }
            $contentsLevel = $value;
        }

        return $fetchedSection;
    }

    public function contents($key = null, $value = null, $type = Obj::VAR_APPEND, $recursive = false, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function section($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function indexDelimiter($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
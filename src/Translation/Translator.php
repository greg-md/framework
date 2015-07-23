<?php

namespace Greg\Translation;

use Greg\Support\Arr;
use Greg\Support\Engine\InternalTrait;
use Greg\Support\Storage\AccessorTrait;
use Greg\Support\Storage\ArrayAccessTrait;
use Greg\Support\Obj;

class Translator implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait, InternalTrait;

    protected $language = 'en';

    protected $defaultLanguage = 'en';

    protected $languages = [];

    protected $languagesUrls = [];

    public function __construct(array $languages = [], array $translates = [])
    {
        $this->languages($languages);

        $this->storage($translates);

        return $this;
    }

    static public function create($appName, array $languages = [], array $translates = [])
    {
        return static::newInstanceRef($appName, $languages, $translates);
    }

    public function isLanguage($language)
    {
        return in_array($language, $this->languages());
    }

    public function isDefault($language)
    {
        return $language == $this->defaultLanguage();
    }

    public function translate($key, ...$args)
    {
        return $this->translateKey($key, $key, ...$args);
    }

    public function translateKey($key, $text, ...$args)
    {
        if (!$this->has($key)) {
            //$this->newTranslates[$key] = $text;
        }

        if (sizeof($args) == 1) {
            $args = Arr::bring($args[0]);
        }

        $customArgs = array_filter($args, function($key) {
            return !is_int($key);
        }, ARRAY_FILTER_USE_KEY);

        $replacements = [];

        foreach($customArgs as $key => $value) {
            $replacements['{' . $key . '}'] = $value;
        }

        $text = strtr($text, $replacements);

        $args = array_filter($args, 'is_int', ARRAY_FILTER_USE_KEY);

        return sprintf($this->get($key, $text), ...$args);
    }

    public function translates($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return $this->storage(...func_get_args());
    }

    public function language($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function defaultLanguage($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function languages($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
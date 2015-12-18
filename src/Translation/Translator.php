<?php

namespace Greg\Translation;

use Greg\Storage\AccessorTrait;
use Greg\Storage\ArrayAccessTrait;
use Greg\Tool\Arr;
use Greg\Tool\Obj;

class Translator implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait;

    protected $language = 'en';

    protected $defaultLanguage = 'en';

    protected $languages = [];

    protected $newTranslates = [];

    public function __construct(array $languages = [], array $translates = [])
    {
        $this->languages($languages);

        $this->storage($translates);

        return $this;
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
            $this->newTranslates($key, $text);
        }

        if (sizeof($args) == 1) {
            $args = Arr::bring($args[0]);
        }

        $replacements = [];

        foreach($args as $key => $value) {
            if (!is_int($key)) {
                $replacements['{' . $key . '}'] = $value;

                unset($args[$key]);
            }
        }

        $text = strtr($text, $replacements);

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

    public function newTranslates($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
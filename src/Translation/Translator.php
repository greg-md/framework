<?php

namespace Greg\Translation;

use Greg\Engine\Internal;
use Greg\Storage\ArrayObject;
use Greg\Support\Obj;

class Translator extends ArrayObject
{
    use Internal;

    protected $language = 'en';

    protected $defaultLanguage = 'en';

    protected $languages = [];

    protected $languagesUrls = [];

    public function has($language)
    {
        return in_array($language, $this->languages());
    }

    public function isDefault($language)
    {
        return $language == $this->defaultLanguage();
    }

    public function translate($key, ...$args)
    {
        return sprintf($this->get($key, $key), ...$args);
    }

    public function translateKey($key, $text, ...$args)
    {
        return sprintf($this->get($key, $text), ...$args);
    }

    public function language($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function defaultLanguage($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function languages($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
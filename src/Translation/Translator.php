<?php

namespace Greg\Translation;

use Greg\Engine\Internal;
use Greg\Engine\InternalInterface;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Translator implements \ArrayAccess, InternalInterface
{
    use ArrayAccess, Internal;

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

    public function translate($key)
    {
        $args = func_get_args();

        $args[0] = $this->get($key, $key);

        return call_user_func_array('sprintf', $args);
    }

    public function translateKey($key, $text)
    {
        $args = func_get_args();

        array_shift($args);

        $args[0] = $this->get($key, $text);

        return call_user_func_array('sprintf', $args);
    }

    public function language($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function defaultLanguage($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function languages($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
<?php

namespace Greg\Translation;

use Greg\Engine\Internal;
use Greg\Storage\Accessor;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Translator implements \ArrayAccess
{
    use Accessor, ArrayAccess, Internal;

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
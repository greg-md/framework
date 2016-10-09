<?php

namespace Greg\Translation;

use Greg\Support\Accessor\ArrayAccessTrait;

class Translator implements \ArrayAccess
{
    use ArrayAccessTrait;

    protected $language = 'en';

    protected $defaultLanguage = 'en';

    protected $languages = [];

    protected $newTranslates = [];

    public function isLanguage($language)
    {
        return in_array($language, $this->getLanguages());
    }

    public function isDefault($language)
    {
        return $language == $this->getDefaultLanguage();
    }

    public function translate($key, ...$args)
    {
        return $this->translateKey($key, $key, ...$args);
    }

    public function translateKey($key, $text, ...$args)
    {
        if (!$this->has($key)) {
            $this->setNewTranslate($key, $text);
        }

        if (count($args) == 1) {
            $args = (array) $args[0];
        }

        $replacements = [];

        foreach ($args as $key => $value) {
            if (!is_int($key)) {
                $replacements['{' . $key . '}'] = $value;

                unset($args[$key]);
            }
        }

        $text = strtr($text, $replacements);

        return sprintf($this->get($key, $text), ...$args);
    }

    public function getTranslates($key = null)
    {
        if (func_num_args()) {
            return $this->getFromAccessor($key);
        }

        return $this->getAccessor();
    }

    public function setTranslate($key, $value)
    {
        return $this->setToAccessor($key, $value);
    }

    public function setTranslates(array $items)
    {
        return $this->addToAccessor($items);
    }

    public function setLanguage($name)
    {
        $this->language = (string) $name;

        return $this;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setDefaultLanguage($name)
    {
        $this->defaultLanguage = (string) $name;

        return $this;
    }

    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    public function setLanguages(array $languages)
    {
        $this->languages = $languages;

        return $this;
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function setNewTranslate($key, $value)
    {
        $this->newTranslates[$key] = $value;

        return $this;
    }

    public function setNewTranslates(array $translates)
    {
        $this->newTranslates = $translates;

        return $this;
    }

    public function getNewTranslates()
    {
        return $this->newTranslates;
    }
}

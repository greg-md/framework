<?php

namespace Greg\Translation;

use Greg\Support\Accessor\AccessorTrait;

class Translator implements TranslatorContract
{
    use AccessorTrait;

    protected $languages = [];

    protected $currentLanguage = 'en';

    protected $defaultLanguage = 'en';

    protected $newTranslates = [];

    public function __construct(array $translates = [])
    {
        $this->addToAccessor($translates);
    }

    public function hasLanguage($language)
    {
        return array_key_exists($language, $this->languages);
    }

    public function getLanguages()
    {
        return $this->languages;
    }

    public function getLanguage($language)
    {
        return $this->hasLanguage($language) ? $this->languages[$language] : null;
    }

    public function getLanguagesKeys()
    {
        return array_keys($this->languages);
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
        if ($this->inAccessor($key)) {
            $text = $this->getFromAccessor($key);
        } else {
            $this->newTranslates[$key] = $text;
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

        return sprintf($text, ...$args);
    }

    public function addTranslate($key, $value)
    {
        return $this->setToAccessor($key, $value);
    }

    public function addTranslates(array $items)
    {
        return $this->addToAccessor($items);
    }

    public function getTranslates()
    {
        return $this->getAccessor();
    }

    public function setCurrentLanguage($name)
    {
        if (!$this->hasLanguage($name)) {
            throw new \Exception('Language `' . $name . '` is not defined in translator.');
        }

        $this->currentLanguage = (string) $name;

        return $this;
    }

    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
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

    public function getNewTranslates()
    {
        return $this->newTranslates;
    }

    public function offsetExists($key)
    {
        return $this->inAccessor($key);
    }

    public function offsetGet($key)
    {
        return $this->getFromAccessor($key);
    }

    public function offsetSet($key, $value)
    {
        return $this->setToAccessor($key, $value);
    }

    public function offsetUnset($key)
    {
        return $this->removeFromAccessor($key);
    }
}

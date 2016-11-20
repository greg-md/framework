<?php

namespace Greg\Translation;

interface TranslatorContract extends \ArrayAccess
{
    public function hasLanguage($language);

    public function getLanguage($language);

    public function getLanguages();

    public function getLanguagesKeys();

    public function isDefault($language);

    public function translate($key, ...$args);

    public function translateKey($key, $text, ...$args);

    public function addTranslate($key, $value);

    public function addTranslates(array $items);

    public function getTranslates();

    public function setLanguages(array $languages);

    public function setCurrentLanguage($name);

    public function getCurrentLanguage();

    public function setDefaultLanguage($name);

    public function getDefaultLanguage();

    public function getNewTranslates();
}

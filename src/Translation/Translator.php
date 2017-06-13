<?php

namespace Greg\Framework\Translation;

class Translator implements \ArrayAccess
{
    private $languages = [];

    private $defaultLanguage = null;

    private $currentLanguage = null;

    private $translatesLoader = [];

    private $translates = [];

    private $newTranslates = [];

    public function hasLanguages(): bool
    {
        return (bool) $this->languages;
    }

    public function getLanguages(): array
    {
        return $this->languages;
    }

    public function clearLanguages()
    {
        $this->languages = [];

        $this->defaultLanguage = null;

        $this->currentLanguage = null;

        $this->translatesLoader = [];

        $this->translates = [];

        return $this;
    }

    public function hasLanguage(string $locale): bool
    {
        return array_key_exists($locale, $this->languages);
    }

    public function getLanguage(string $locale): array
    {
        return $this->hasLanguage($locale) ? $this->languages[$locale] : null;
    }

    public function addLanguage(string $locale, array $details = [], callable $translatesLoader = null)
    {
        $this->languages[$locale] = $details;

        if ($translatesLoader) {
            $this->translatesLoader[$locale] = $translatesLoader;
        }

        return $this;
    }

    public function removeLanguage(string $locale)
    {
        unset($this->languages[$locale]);

        if ($this->defaultLanguage === $locale) {
            $this->defaultLanguage = null;
        }

        if ($this->currentLanguage === $locale) {
            $this->currentLanguage = $this->defaultLanguage;
        }

        unset($this->translatesLoader[$locale]);

        unset($this->translates[$locale]);

        return $this;
    }

    public function getLocales(): array
    {
        return array_keys($this->languages);
    }

    public function setDefaultLanguage(string $locale)
    {
        $this->checkLocaleIfExists($locale);

        $this->defaultLanguage = (string) $locale;

        return $this;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage ?: $this->findLocale();
    }

    public function setCurrentLanguage(string $locale)
    {
        $this->checkLocaleIfExists($locale);

        $this->currentLanguage = (string) $locale;

        return $this;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage ?: $this->findLocale();
    }

    public function hasTranslatesLoaders(): bool
    {
        return (bool) $this->translatesLoader;
    }

    public function getTranslatesLoaders(): array
    {
        return $this->translatesLoader;
    }

    public function clearTranslatesLoaders()
    {
        $this->translatesLoader = [];

        return $this;
    }

    public function hasTranslateLoader(string $locale): bool
    {
        return array_key_exists($locale, $this->translatesLoader);
    }

    public function getTranslateLoader(string $locale): ?callable
    {
        return $this->translatesLoader[$locale] ?? null;
    }

    public function addTranslateLoader(string $locale, callable $loader)
    {
        $this->checkLocaleIfExists($locale);

        $this->translatesLoader[$locale] = $loader;

        return $this;
    }

    public function removeTranslateLoader(string $locale)
    {
        unset($this->translatesLoader[$locale]);

        return $this;
    }

    public function hasAnyTranslates(): bool
    {
        return (bool) $this->translates;
    }

    public function getAllTranslates(): array
    {
        return $this->translates;
    }

    public function clearTranslates()
    {
        $this->translates = [];

        return $this;
    }

    public function hasTranslates(string $locale): bool
    {
        return (bool) ($this->translates[$locale] ?? false);
    }

    public function getTranslates(string $locale): array
    {
        return $this->translates[$locale] ?? [];
    }

    public function setTranslates(string $locale, array $translates)
    {
        $this->checkLocaleIfExists($locale);

        $this->translates[$locale] = $translates;

        return $this;
    }

    public function addTranslates(string $locale, array $translates)
    {
        $this->checkLocaleIfExists($locale);

        $this->translates[$locale] = array_merge($this->translates[$locale] ?? [], $translates);

        return $this;
    }

    public function removeTranslates(string $locale)
    {
        unset($this->translates[$locale]);

        return $this;
    }

    public function hasTranslate(string $locale, string $key): bool
    {
        return array_key_exists($key, $this->translates[$locale] ?? []);
    }

    public function getTranslate(string $locale, string $key): string
    {
        return $this->hasTranslate($locale, $key) ? $this->translates[$locale][$key] : null;
    }

    public function addTranslate(string $locale, string $key, $value)
    {
        $this->checkLocaleIfExists($locale);

        $this->translates[$locale][$key] = $value;

        return $this;
    }

    public function removeTranslate(string $locale, string $key)
    {
        unset($this->translates[$locale][$key]);

        return $this;
    }

    public function addLocaleLanguage()
    {
        $this->addLanguage($this->findLocale());

        return $this;
    }

    public function isDefault(string $locale): bool
    {
        return $locale == $this->getDefaultLanguage();
    }

    public function currentTranslates(): array
    {
        return $this->translates[$this->getCurrentLanguage()] ?? [];
    }

    public function translate(string $key, ...$arguments): string
    {
        return $this->translateKey($key, $key, ...$arguments);
    }

    public function translateKey(string $key, string $default, ...$arguments): string
    {
        if (count($arguments) == 1) {
            $arguments = (array) $arguments[0];
        }

        return TranslateText::applyArguments($this->findText($key, $default), $arguments);
    }

    public function newTranslates(): array
    {
        return $this->newTranslates;
    }

    public function offsetExists($key)
    {
        return $this->hasTranslate($this->getCurrentLanguage(), $key);
    }

    public function offsetGet($key)
    {
        return new TranslateText($this->getTranslate($this->getCurrentLanguage(), $key));
    }

    public function offsetSet($key, $value)
    {
        $this->addTranslate($this->getCurrentLanguage(), $key, $value);
    }

    public function offsetUnset($key)
    {
        return $this->removeTranslate($this->getCurrentLanguage(), $key);
    }

    public function __invoke(string $key, ...$arguments): string
    {
        return $this->translate($key, ...$arguments);
    }

    private function checkLocaleIfExists(string $locale)
    {
        if (!$this->hasLanguage($locale)) {
            throw new \Exception('Language `' . $locale . '` is not defined in Translator.');
        }

        return $this;
    }

    private function findLocale(): string
    {
        return setlocale(LC_CTYPE, 0);
    }

    private function findText(string $key, string $default): string
    {
        if (array_key_exists($key, $translates = $this->currentTranslates())) {
            return $translates[$key];
        }

        $this->newTranslates[$this->getCurrentLanguage()][$key] = $default;

        return $default;
    }
}

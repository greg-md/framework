<?php

namespace Greg\Framework\Translation;

class TranslateText
{
    private $text;

    public function __construct(string $text)
    {
        $this->text = $text;

        return $this;
    }

    public function apply(...$arguments)
    {
        if (count($arguments) == 1) {
            $arguments = (array) $arguments[0];
        }

        return $this->applyArguments($this->text, $arguments);
    }

    public static function applyArguments(string $text, array $arguments)
    {
        $replacements = [];

        foreach ($arguments as $key => $value) {
            if (!is_int($key)) {
                $replacements['{' . $key . '}'] = $value;

                unset($arguments[$key]);
            }
        }

        $text = strtr($text, $replacements);

        return sprintf($text, ...$arguments);
    }

    public function __invoke(...$arguments)
    {
        return $this->apply(...$arguments);
    }

    public function __toString()
    {
        return $this->text;
    }
}

<?php

namespace Greg\Html;

use Greg\Storage\AccessorTrait;
use Greg\Tool\Arr;

class HtmlScript
{
    use AccessorTrait;

    const APPEND = 'append';

    const PREPEND = 'prepend';

    const ADD_BEFORE = 'before';

    const ADD_INNER = 'inner';

    const ADD_AFTER = 'after';

    const ORDER = [self::ADD_BEFORE, self::ADD_INNER, self::ADD_AFTER];

    public function appendSrc($src, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_INNER, static::APPEND, $src, $condition, $attr);
    }

    public function prependSrc($src, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_INNER, static::PREPEND, $src, $condition, $attr);
    }

    public function appendText($text, $condition = null, array $attr = [])
    {
        return $this->addText(self::ADD_INNER, static::APPEND, $text, $condition, $attr);
    }

    public function prependText($text, $condition = null, array $attr = [])
    {
        return $this->addText(self::ADD_INNER, static::PREPEND, $text, $condition, $attr);
    }

    public function appendSrcBefore($src, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_BEFORE, static::APPEND, $src, $condition, $attr);
    }

    public function prependSrcBefore($src, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_BEFORE, static::PREPEND, $src, $condition, $attr);
    }

    public function appendTextBefore($text, $condition = null, array $attr = [])
    {
        return $this->addText(self::ADD_BEFORE, static::APPEND, $text, $condition, $attr);
    }

    public function prependTextBefore($text, $condition = null, array $attr = [])
    {
        return $this->addText(self::ADD_BEFORE, static::PREPEND, $text, $condition, $attr);
    }

    public function appendSrcAfter($src, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_AFTER, static::APPEND, $src, $condition, $attr);
    }

    public function prependSrcAfter($src, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_AFTER, static::PREPEND, $src, $condition, $attr);
    }

    public function appendTextAfter($text, $condition = null, array $attr = [])
    {
        return $this->addText(self::ADD_AFTER, static::APPEND, $text, $condition, $attr);
    }

    public function prependTextAfter($text, $condition = null, array $attr = [])
    {
        return $this->addText(self::ADD_AFTER, static::PREPEND, $text, $condition, $attr);
    }

    protected function addSrc($where, $type, $sources, $condition = null, array $attr = [])
    {
        Arr::bringRef($sources);

        foreach($sources as $src) {
            $thisAttr = $attr;

            $thisAttr['src'] = $src;

            $param = [
                'inner' => null,
                'condition' => $condition,
                'attr' => $thisAttr,
            ];

            if ($type == static::PREPEND) {
                $this->storage[$where] = array_merge([$param], $this->storage[$where]);
            } else {
                $this->storage[$where][] = $param;
            }
        }

        return $this;
    }

    protected function addText($where, $type, $texts, $condition = null, array $attr = [])
    {
        Arr::bringRef($texts);

        foreach($texts as $text) {
            $param = [
                'inner' => $text,
                'condition' => $condition,
                'attr' => $attr,
            ];

            if ($type == static::PREPEND) {
                $this->storage[$where] = array_merge([$param], $this->storage[$where]);
            } else {
                $this->storage[$where][] = $param;
            }
        }

        return $this;
    }

    public function toObjects()
    {
        $html = [];

        foreach(static::ORDER as $type) {
            foreach((array)$this->getFromStorage($type) as $item) {
                $element = new HtmlElement('script', $item['attr'], $item['condition']);

                $element->inner($item['inner']);

                $html[] = $element;
            }
        }

        return $html;
    }

    public function toString()
    {
        return implode("\n", $this->toObjects());
    }

    public function __toString()
    {
        return $this->toString();
    }
}
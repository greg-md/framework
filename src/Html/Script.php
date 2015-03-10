<?php

namespace Greg\Html;

use Greg\Engine\Internal;
use Greg\Support\Obj;

class Script
{
    use Internal;

    const APPEND = 'append';

    const PREPEND = 'prepend';

    const ADD_BEFORE = 'before';

    const ADD_INNER = 'inner';

    const ADD_AFTER = 'after';

    protected $order = [self::ADD_BEFORE, self::ADD_INNER, self::ADD_AFTER];

    protected $storage = [];

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
        return $this->addSrc(self::ADD_INNER, static::APPEND, $text, $condition, $attr);
    }

    public function prependText($text, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_INNER, static::PREPEND, $text, $condition, $attr);
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
        return $this->addSrc(self::ADD_BEFORE, static::APPEND, $text, $condition, $attr);
    }

    public function prependTextBefore($text, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_BEFORE, static::PREPEND, $text, $condition, $attr);
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
        return $this->addSrc(self::ADD_AFTER, static::APPEND, $text, $condition, $attr);
    }

    public function prependTextAfter($text, $condition = null, array $attr = [])
    {
        return $this->addSrc(self::ADD_AFTER, static::PREPEND, $text, $condition, $attr);
    }

    protected function addSrc($where, $type, $src, $condition = null, array $attr = [])
    {
        foreach((array)($sources = $src) as $src) {
            $thisAttr = $attr;
            $thisAttr['src'] = $src;
            $param = [
                'condition' => $condition,
                'attr' => $thisAttr,
            ];
            if ($type == static::PREPEND) {
                $this->storage[$where] = array_merge([$param], $this->storage['inner']);
            } else {
                $this->storage[$where][] = $param;
            }
        }

        return $this;
    }

    protected function addText($where, $type, $text, $condition = null, array $attr = [])
    {
        foreach((array)($texts = $text) as $text) {
            $param = [
                'inner' => $text,
                'condition' => $condition,
                'attr' => $attr,
            ];
            if ($type == static::PREPEND) {
                $this->storage[$where] = array_merge([$param], $this->storage['inner']);
            } else {
                $this->storage[$where][] = $param;
            }
        }

        return $this;
    }

    public function order($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false, $recursive = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function fetchItem($item)
    {
        $attr = isset($item['attr']) ? $item['attr'] : [];

        $condition = isset($item['condition']) ? $item['condition'] : null;

        $element = Element::create($this->appName(), 'script', $attr, $condition);

        if (isset($item['inner']) and $item['inner']) {
            $element->inner($item['inner']);
        }

        return $element;
    }

    public function __toString()
    {
        $html = [];

        foreach($this->order() as $type) {
            if (isset($this->storage[$type])) {
                foreach($this->storage[$type] as $item) {
                    $html[] = $this->fetchItem($item);
                }
            }
        }

        return implode('', $html);
    }
}
<?php

namespace Greg\Framework\Html;

class HtmlElement implements \ArrayAccess
{
    const SHORT_TAGS = 'h1,h2,h3,h4,h5,h6,input,hr,br,link,meta,img,keygen';

    protected $name;

    protected $attributes = [];

    private $content;

    private $condition;

    public function __construct($name = null, array $attributes = [], string $condition = null)
    {
        if ($name !== null) {
            $this->name = $name;
        }

        if ($attributes) {
            $this->attributes = array_merge($this->attributes, $attributes);
        }

        if ($condition !== null) {
            $this->setCondition($condition);
        }

        return $this;
    }

    public function name()
    {
        return $this->name;
    }

    public function hasAttributes(): bool
    {
        return (bool) $this->attributes;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function clearAttributes()
    {
        $this->attributes = [];

        return $this;
    }

    public function hasAttribute(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function getAttribute(string $name): ?string
    {
        return $this->hasAttribute($name) ? $this->attributes[$name] : null;
    }

    public function addAttribute(string $name, string $value = null)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function removeAttribute(string $name)
    {
        unset($this->attributes[$name]);

        return $this;
    }

    public function setContent(string $content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setCondition($condition)
    {
        $this->condition = (string) $condition;

        return $this;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function startTag()
    {
        $attr = $this->attrToString();

        return '<' . $this->name() . ($attr ? ' ' . $attr : '') . ($this->isShortElement() ? ' /' : '') . '>';
    }

    public function endTag()
    {
        return $this->isShortElement() ? null : '</' . $this->name() . '>';
    }

    public function toString()
    {
        $string = $this->startTag() . $this->content . $this->endTag();

        if ($condition = $this->getCondition()) {
            $string = '<!--[if ' . $condition . ']>' . $string . '<![endif]-->';
        }

        return $string;
    }

    public function offsetExists($offset)
    {
        return $this->hasAttribute($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->addAttribute($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->removeAttribute($offset);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public static function cleanupAttribute(string $content)
    {
        return htmlspecialchars(preg_replace('#\n+#', ' ', $content));
    }

    private function attrToString()
    {
        $attr = [];

        foreach ($this->attributes as $key => $value) {
            $value = $this->cleanupAttribute($value);

            if ($value !== null || $value !== '') {
                $key .= '="' . $value . '"';
            }

            $attr[] = $key;
        }

        return implode(' ', $attr);
    }

    private function isShortElement()
    {
        return in_array($this->name(), explode(',', static::SHORT_TAGS));
    }
}

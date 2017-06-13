<?php

namespace Greg\Framework\Html;

class HtmlElement implements \ArrayAccess
{
    const SHORT_TAGS = 'h1,h2,h3,h4,h5,h6,input,hr,br,link,meta,img,keygen';

    private $name;

    private $attributes = [];

    private $content;

    private $condition;

    public function __construct($name, array $attributes = [], string $condition = null)
    {
        $this->name = $name;

        $this->attributes = $attributes;

        $this->condition = $condition;

        return $this;
    }

    public function name(): string
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

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function addAttributes(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setCondition(string $condition)
    {
        $this->condition = $condition;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function startTag(): string
    {
        $attr = $this->attrToString();

        return '<' . $this->name() . ($attr ? ' ' . $attr : '') . ($this->isShortElement() ? ' /' : '') . '>';
    }

    public function endTag(): ?string
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

    public static function cleanupAttribute(string $content): string
    {
        return htmlspecialchars(preg_replace('#\n+#', ' ', $content));
    }

    private function attrToString(): string
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

    private function isShortElement(): bool
    {
        return in_array($this->name(), explode(',', static::SHORT_TAGS));
    }
}

<?php

namespace Greg\Html;

use Greg\Support\Accessor\AccessorTrait;
use Greg\Support\Accessor\ArrayAccessTrait;

class HtmlElement implements \ArrayAccess
{
    use AccessorTrait, ArrayAccessTrait;

    const SHORT_TAGS = 'h1,h2,h3,h4,h5,h6,input,hr,br,link,meta,img,keygen';

    protected $name = null;

    protected $inner = null;

    protected $before = null;

    protected $after = null;

    protected $condition = null;

    public function __construct($name = null, array $attr = [], $condition = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }

        if ($attr) {
            $this->addToAccessor($attr);
        }

        if ($condition !== null) {
            $this->setCondition($condition);
        }

        return $this;
    }

    public static function clearAttrValue($content)
    {
        return htmlspecialchars(preg_replace('#\n+#', ' ', trim(strip_tags($content))));
    }

    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }

    public function getName()
    {
        if (!$this->name) {
            throw new \Exception('Undefined tag name.');
        }

        return $this->name;
    }

    public function setInner($html)
    {
        $this->inner = (string) $html;

        return $this;
    }

    public function getInner()
    {
        return $this->inner;
    }

    public function setBefore($html)
    {
        $this->before = (string) $html;

        return $this;
    }

    public function getBefore()
    {
        return $this->before;
    }

    public function setAfter($html)
    {
        $this->after = (string) $html;

        return $this;
    }

    public function getAfter()
    {
        return $this->after;
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

        return '<' . $this->getName() . ($attr ? ' ' . $attr : '') . ($this->short() ? ' /' : '') . '>';
    }

    protected function attrToString()
    {
        $attr = [];

        foreach ($this->getAccessor() as $key => $value) {
            $value = htmlspecialchars((string) $value);

            if ($value != '') {
                $key .= '="' . $value . '"';
            }

            $attr[] = $key;
        }

        return implode(' ', $attr);
    }

    protected function short()
    {
        return in_array($this->getName(), explode(',', static::SHORT_TAGS));
    }

    public function endTag()
    {
        return !$this->short() ? '</' . $this->getName() . '>' : null;
    }

    public function toString()
    {
        $string = $this->startTag() . $this->getInner() . $this->endTag();

        if ($condition = $this->getCondition()) {
            $string = '<!--[if ' . $condition . ']>' . $string . '<![endif]-->';
        }

        return $this->getBefore() . $string . $this->getAfter();
    }

    public function __toString()
    {
        return $this->toString();
    }
}

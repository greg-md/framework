<?php

namespace Greg\Html;

use Greg\Storage\AccessorTrait;
use Greg\Storage\ArrayAccessTrait;
use Greg\Tool\Obj;

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
            $this->name($name);
        }

        if ($attr) {
            $this->storage($attr);
        }

        if ($condition !== null) {
            $this->condition($condition);
        }

        return $this;
    }

    static public function clearAttrValue($content)
    {
        return htmlspecialchars(preg_replace('#\n+#', ' ', trim(strip_tags($content))));
    }

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function inner($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function before($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function after($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function condition($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function startTag()
    {
        $attr = $this->attrToString();

        return '<' . $this->getName() . ($attr ? ' ' . $attr : '') . ($this->short() ? ' /' : '') . '>';
    }

    protected function attrToString()
    {
        $attr = [];

        foreach ($this->storage as $key => $value) {
            $value = htmlspecialchars((string)$value);

            if ($value != '') {
                $key .= '="' . $value . '"';
            }

            $attr[] = $key;
        }

        return implode(' ', $attr);
    }

    protected function short()
    {
        return in_array($this->name(), explode(',', static::SHORT_TAGS));
    }

    protected function getName()
    {
        $name = $this->name();

        if (!$name) {
            throw new \Exception('Undefined tag name.');
        }

        return $name;
    }

    public function endTag()
    {
        return !$this->short() ? '</' . $this->getName() . '>' : null;
    }

    public function toString()
    {
        $string = $this->startTag() . $this->inner() . $this->endTag();

        if ($condition = $this->condition()) {
            $string = '<!--[if ' . $condition . ']>' . $string . '<![endif]-->';
        }

        return $this->before() . $string . $this->after();
    }

    public function __call($method, $args)
    {
        return $this->storage($method, ...$args);
    }

    public function __toString()
    {
        return $this->toString();
    }
}
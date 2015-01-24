<?php

namespace Greg\Html;

use Greg\Engine\Internal;
use Greg\Storage\ArrayAccess;
use Greg\Support\Obj;

class Element implements \ArrayAccess
{
    use ArrayAccess, Internal;

    const SHORT_TAGS = 'h1,h2,h3,h4,h5,h6,input,hr,br,link,meta,img,keygen';

    protected $name = null;

    protected $inner = null;

    protected $before = null;

    protected $after = null;

    public function __construct($name = null, array $attr = [])
    {
        if ($name !== null) {
            $this->name($name);
        }

        if ($attr) {
            $this->attr($attr);
        }

        $this->subscribe();

        return $this;
    }

    public function subscribe()
    {
        //$listener = $this->getListener();

        //v($listener);

        return $this;
    }

    public function &attr($key = null, $value = null)
    {
        $numArgs = func_num_args();
        if ($numArgs > 0) {
            if (is_array($key)) {
                foreach(($attr = $key) as $key => $value) {
                    $this->set($key, $value);
                }

                return $this;
            }

            if ($numArgs > 1) {
                $this->set($key, $value);

                return $this;
            }

            return $this->get($key);
        }

        return $this->storage;
    }

    public function name($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function inner($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function before($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function after($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
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
            throw new Exception('Undefined tag name.');
        }
        return $name;
    }

    public function endTag()
    {
        return !$this->short() ? '</' . $this->getName() . '>' : null;
    }

    public function toString()
    {
        return $this->before() . $this->startTag() . $this->inner() . $this->endTag() . $this->after();
    }

    public function &__call($method, $args)
    {
        return $args ? $this->attr($method, current($args)) : $this->attr($method);
    }

    public function __toString()
    {
        return $this->toString();
    }
}
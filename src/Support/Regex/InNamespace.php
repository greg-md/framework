<?php

namespace Greg\Support\Regex;

use Greg\Support\Obj;

class InNamespace
{
    protected $start = null;

    protected $end = null;

    protected $recursive = true;

    protected $capture = true;

    protected $allowEmpty = true;

    protected $match = null;

    public function __construct($start, $end = null, $recursive = null)
    {
        $this->start($start);

        if ($end === null) {
            $end = $start;
        }

        $this->end($end);

        if ($recursive !== null) {
            $this->recursive($recursive);
        }

        return $this;
    }

    public function toString()
    {
        $noEscaped = "(?<!\\\\)";

        $captureS = $this->capture() ? '(' : '';
        $captureE = $this->capture() ? ')' : '';

        $matches = [
            $this->match() ?: "(?:\\\\\\{$this->start()}|\\\\\\{$this->end()}|[^\\{$this->start()}\\{$this->end()}])",
        ];

        if ($this->recursive()) {
            $matches[] = '(?R)';
        }

        $matches = implode('|', $matches);

        $flag = $this->allowEmpty() ? '*' : '+';

        return "{$noEscaped}\\{$this->start()}{$captureS}(?>{$matches}){$flag}{$captureE}{$noEscaped}\\{$this->end()}";

        // match [some \[escaped\] var]
        /* (?<!\\)\[((?>(?:\\\[|\\\]|[^\[\]])|(?R))*)(?<!\\)\] */
    }

    public function __toString()
    {
        return (string)$this->toString();
    }

    public function start($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function end($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function recursive($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function capture($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function allowEmpty($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function match($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
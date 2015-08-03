<?php

namespace Greg\Support\Testing;

class Test
{
    protected $condition = null;

    protected $expected = null;

    protected $result = null;

    public function __construct($condition = null)
    {
        if (func_num_args()) {
            $this->condition($condition);
        }

        return $this;
    }

    public function expect($result)
    {
        $this->expected($result);

        return $this;
    }

    public function toBe($expected)
    {
        $this->result($this->expected() == $expected);

        return $this;
    }

    public function toEqual($expected)
    {
        $this->result($this->expected() === $expected);

        return $this;
    }

    public function condition($value = null)
    {
        if (func_num_args()) {
            $this->condition = $value;

            return $this;
        }

        return $this->condition;
    }

    public function expected($value = null)
    {
        if (func_num_args()) {
            $this->expected = $value;

            return $this;
        }

        return $this->expected;
    }

    public function result($value = null)
    {
        if (func_num_args()) {
            $this->expected = (bool)$value;

            return $this;
        }

        return (bool)$this->expected;
    }

    public function failed()
    {
        return $this->result() === false;
    }

    public function succeed()
    {
        return $this->result() === true;
    }

    public function __toString()
    {
        $return = $this->condition();

        $result = $this->result();
        
        $return .= ': ' . (is_bool($result) ? ($result ? 'true' : 'false') : 'not tested');

        return (string)$return;
    }
}

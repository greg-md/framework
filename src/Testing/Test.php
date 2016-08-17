<?php

namespace Greg\Testing;

class Test
{
    protected $condition = null;

    protected $expected = null;

    protected $result = null;

    public function expect($result)
    {
        return $this->setExpected($result);
    }

    public function toBe($expected)
    {
        $this->result = $this->getExpected() == $expected;

        return $this;
    }

    public function toEqual($expected)
    {
        $this->result = ($this->getExpected() === $expected);

        return $this;
    }

    public function setCondition($value)
    {
        $this->condition = $value;

        return $this;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function setExpected($value)
    {
        $this->expected = $value;

        return $this;
    }

    public function getExpected()
    {
        return $this->expected;
    }

    public function failed()
    {
        return $this->result === false;
    }

    public function succeed()
    {
        return $this->result === true;
    }

    public function toString()
    {
        return $this->getCondition() . ': ' . (is_bool($this->result) ? ($this->result ? 'true' : 'false') : 'not tested');
    }

    public function __toString()
    {
        return $this->toString();
    }
}

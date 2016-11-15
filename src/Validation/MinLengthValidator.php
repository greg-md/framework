<?php

namespace Greg\Validation;

class MinLengthValidator implements ValidatorStrategy
{
    use ValidatorTrait;

    protected $length = null;

    public function __construct($length)
    {
        $this->setLength($length);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $length = $this->getLength();

        if (mb_strlen($value) < $length) {
            $this->setError('MinLength', 'Value length should be grater or equal with ' . $length . '.');

            return false;
        }

        return true;
    }

    public function setLength($length)
    {
        $this->length = (int) $length;

        return $this;
    }

    public function getLength()
    {
        return $this->length;
    }
}

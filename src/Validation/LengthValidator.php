<?php

namespace Greg\Validation;

class LengthValidator implements ValidatorStrategy
{
    use ValidatorTrait;

    protected $length = 0;

    public function __construct($length)
    {
        $this->setLength($length);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $length = $this->getLength();

        if (mb_strlen($value) != $length) {
            $this->setError('LengthError', 'Value length should be ' . $length . '.');

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

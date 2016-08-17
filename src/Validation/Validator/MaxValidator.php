<?php

namespace Greg\Validation\Validator;

use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class MaxValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $max = null;

    public function __construct($max)
    {
        $this->setMax($max);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $max = $this->getMax();

        if ($value > $max) {
            $this->setError('MaxError', 'Value should be less or equal with ' . $max . '.');

            return false;
        }

        return true;
    }

    public function setMax($length)
    {
        $this->max = (int)$length;

        return $this;
    }

    public function getMax()
    {
        return $this->max;
    }
}
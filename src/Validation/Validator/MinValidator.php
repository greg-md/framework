<?php

namespace Greg\Validation\Validator;

use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class MinValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $min = null;

    public function __construct($min)
    {
        $this->setMin($min);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $min = $this->getMin();

        if ($value < $min) {
            $this->setError('MinError', 'Value should be grater or equal with ' . $min . '.');

            return false;
        }

        return true;
    }

    public function setMin($length)
    {
        $this->min = (int)$length;

        return $this;
    }

    public function getMin()
    {
        return $this->min;
    }
}
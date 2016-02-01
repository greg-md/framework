<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class MinValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $min = null;

    public function __construct($min)
    {
        $this->min($min);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $errors = [];

        $min = $this->min();

        if ($value < $min) {
            $errors[] = 'Value should be grater or equal with ' . $min . '.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function min($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }
}
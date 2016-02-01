<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class MaxValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $max = null;

    public function __construct($max)
    {
        $this->max($max);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $errors = [];

        $max = $this->max();

        if ($value > $max) {
            $errors[] = 'Value should be less or equal with ' . $max . '.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function max($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }
}
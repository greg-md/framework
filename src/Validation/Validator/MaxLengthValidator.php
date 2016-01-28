<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class MaxLengthValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $length = null;

    public function __construct($length)
    {
        $this->length($length);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $errors = [];

        $length = $this->length();

        if (mb_strlen($value) > $length) {
            $errors[] = 'Value length should be less than ' . $length . '.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function length($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }
}
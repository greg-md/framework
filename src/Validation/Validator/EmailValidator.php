<?php

namespace Greg\Validation\Validator;

use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class EmailValidator implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate($value)
    {
        $errors = [];

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Value is not an email.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }
}
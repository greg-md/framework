<?php

namespace Greg\Validation\Validator;

use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class RequiredValidator implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate($value)
    {
        $errors = [];

        if (!$value) {
            $errors[] = 'Value is required.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }
}
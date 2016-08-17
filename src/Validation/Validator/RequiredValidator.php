<?php

namespace Greg\Validation\Validator;

use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class RequiredValidator implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate($value, array $values = [])
    {
        if (!$value) {
            $this->setError('RequiredError', 'Value is required.');

            return false;
        }

        return true;
    }
}
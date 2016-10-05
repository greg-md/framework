<?php

namespace Greg\Validation;

class EmailValidator implements ValidatorInterface
{
    use ValidatorTrait;

    public function validate($value, array $values = [])
    {
        if (!$value) {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->setError('EmailError', 'Value is not an email.');

            return false;
        }

        return true;
    }
}

<?php

namespace Greg\Validation;

class RequiredValidator implements ValidatorStrategy
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

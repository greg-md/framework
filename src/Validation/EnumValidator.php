<?php

namespace Greg\Validation;

class EnumValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $values = [];

    public function __construct(array $values)
    {
        $this->setValues($values);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        if (!in_array($value, $this->getValues())) {
            $this->setError('EnumError', 'Value is not found in the enum.');

            return false;
        }

        return true;
    }

    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }
}
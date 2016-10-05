<?php

namespace Greg\Validation;

trait ValidatorTrait
{
    protected $errors = [];

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors(array $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function setError($key, $message)
    {
        $this->errors[$key] = $message;

        return $this;
    }
}

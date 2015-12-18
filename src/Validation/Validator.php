<?php

namespace Greg\Validation;

use Greg\Tool\Arr;

class Validator
{
    protected $errors = [];

    protected $params = [];

    public function __construct(array $validators = [])
    {

    }

    public function validate(array $params = [])
    {
        $this->params = $params;

        return true;
    }

    public function getParam($name, $else = null)
    {
        return Arr::get($this->params, $name, $else);
    }

    public function getAll()
    {
        return $this->params;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
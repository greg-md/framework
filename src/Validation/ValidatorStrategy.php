<?php

namespace Greg\Validation;

interface ValidatorStrategy
{
    public function validate($value, array $values = []);

    public function getErrors();
}

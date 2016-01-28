<?php

namespace Greg\Validation;

interface ValidatorInterface
{
    public function validate($value, array $values = []);

    public function getErrors();
}
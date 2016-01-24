<?php

namespace Greg\Validation;

interface ValidatorInterface
{
    public function validate($value);

    public function getErrors();
}
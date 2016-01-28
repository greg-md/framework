<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Arr;
use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class SameAsValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $sameAs = null;

    public function __construct($sameAs)
    {
        $this->sameAs($sameAs);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $errors = [];

        $sameAs = $this->sameAs();

        if ($value !== Arr::get($values, $sameAs)) {
            $errors[] = 'Value is not the same as `' . $sameAs . '`.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function sameAs($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
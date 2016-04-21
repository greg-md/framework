<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Arr;
use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class EnumValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $values = [];

    public function __construct(array $values)
    {
        $this->values($values);

        return $this;
    }

    public function validate($value, array $values = [])
    {
        $errors = [];

        if (!in_array($value, $this->values())) {
            $errors[] = 'Value is not found in the enum.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    protected function values($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayReplaceVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
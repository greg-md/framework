<?php

namespace Greg\Validation\Validator;

use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class IntValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $type = null;

    public function __construct($type = null)
    {
        if ($type !== null) {
            $this->type($type);
        }

        return $this;
    }

    public function validate($value)
    {
        $errors = [];

        if ($value != (int)$value) {
            $errors[] = 'Value is not integer.';

            if ($this->type() === 'unsigned' and $value < 0) {
                $errors[] = 'Value is not unsigned.';
            }
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function type($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
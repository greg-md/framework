<?php

namespace Greg\Validation\Validator;

use Greg\System\DateTime;
use Greg\Tool\Obj;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class DateTimeFromValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $from = null;

    protected $includeFrom = true;

    public function __construct($from, $includeFrom = null)
    {
        $this->from($from);

        if ($includeFrom !== null) {
            $this->includeFrom($includeFrom);
        }

        return $this;
    }

    public function validate($value)
    {
        if (!$value) {
            return true;
        }

        $errors = [];

        $value = DateTime::toTimestamp($value);

        $from = DateTime::toTimestamp($this->from());

        if ($this->includeFrom() ? $value >= $from : $value > $from) {
            $errors[] = 'Value should be greater than ' . DateTime::toStringDateTime($from) . '.';
        }

        if ($errors) {
            $this->errors($errors, true);

            return false;
        }

        return true;
    }

    public function from($value = null)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function includeFrom($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
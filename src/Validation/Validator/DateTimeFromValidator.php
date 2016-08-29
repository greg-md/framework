<?php

namespace Greg\Validation\Validator;

use Greg\System\DateTime;
use Greg\Validation\ValidatorInterface;
use Greg\Validation\ValidatorTrait;

class DateTimeFromValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $from = null;

    protected $includeFrom = true;

    public function __construct($from, $includeFrom = null)
    {
        $this->setFrom($from);

        if ($includeFrom !== null) {
            $this->includeFrom($includeFrom);
        }

        return $this;
    }

    public function validate($value, array $values = [])
    {
        if (!$value) {
            return true;
        }

        $value = DateTime::toTimestamp($value);

        $from = DateTime::toTimestamp($this->getFrom());

        if ($this->includeFrom() ? $value <= $from : $value < $from) {
            $this->setError('DateTimeFromError', 'Value should be greater than ' . DateTime::toDateTimeString($from) . '.');

            return false;
        }

        return true;
    }

    public function setFrom($datetime)
    {
        $this->from = (string)$datetime;

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function includeFrom($value = null)
    {
        if (func_num_args()) {
            $this->includeFrom = (bool)$value;

            return $this;
        }

        return $this->includeFrom;
    }
}
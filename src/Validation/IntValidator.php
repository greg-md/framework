<?php

namespace Greg\Validation;

class IntValidator implements ValidatorInterface
{
    use ValidatorTrait;

    protected $unsigned = false;

    public function __construct($unsigned = null)
    {
        if ($unsigned !== null) {
            $this->unsigned($unsigned);
        }

        return $this;
    }

    public function validate($value, array $values = [])
    {
        if ($value != (int) $value) {
            $this->setError('IntError', 'Value is not integer.');

            return false;
        }

        if ($this->unsigned() and $value < 0) {
            $this->setError('IntUnsignedError', 'Value is not unsigned.');

            return false;
        }

        return true;
    }

    public function unsigned($value = null)
    {
        if (func_num_args()) {
            if (!is_bool($value)) {
                $value = ($value === 'unsigned');
            }

            $this->unsigned = (bool) $value;

            return $this;
        }

        return $this->unsigned;
    }
}

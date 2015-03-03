<?php

namespace Greg\Db\Sql\Table;

use Greg\Engine\Internal;
use Greg\Support\Obj;

class Column
{
    use Internal;

    const TYPE_TINYINT = 1;

    const TYPE_SMALLINT = 2;

    const TYPE_MEDIUMINT = 3;

    const TYPE_INT = 4;

    const TYPE_BIGINT = 8;

    static protected $numericTypes = [
        'tinyint' => self::TYPE_TINYINT,
        'smallint' => self::TYPE_SMALLINT,
        'mediumint' => self::TYPE_MEDIUMINT,
        'int' => self::TYPE_INT,
        'bigint' => self::TYPE_BIGINT,
    ];

    protected $name = null;

    protected $type = 'int';

    protected $length = null;

    protected $isUnsigned = false;

    protected $allowNull = true;

    protected $def = null;

    protected $comment = null;

    protected $values = [];

    public function __construct($name, $type = null, $length = null, $isUnsigned = null, $allowNull = null, $def = null, $comment = null)
    {
        $this->name($name);

        if ($type !== null) {
            $this->type($type);
        }

        if ($length !== null) {
            if (is_array($length)) {
                $this->values($length);
            } else {
                $this->length($length);
            }
        }

        if ($isUnsigned !== null) {
            $this->isUnsigned($isUnsigned);
        }

        if ($allowNull !== null) {
            $this->allowNull($allowNull);
        }

        if ($def !== null) {
            $this->def($def);
        }

        if ($comment !== null) {
            $this->comment($comment);
        }

        return $this;
    }

    static public function getNumericLength($type)
    {
        return isset(static::$numericTypes[$type]) ? static::$numericTypes[$type] : null;
    }

    static public function isNumeric($type)
    {
        return static::getNumericLength($type) !== null;
    }

    public function getMinValue()
    {
        if ($this->isNumeric($this->type())) {
            if ($this->unsigned()) {
                return 0;
            }

            return ($this->getMaxValue() + 1) * -1;
        }

        return null;
    }

    public function getMaxValue()
    {
        if ($len = $this->getNumericLength($this->type())) {
            $maxValue = pow(16, $len * 2);

            if (!$this->unsigned()) {
                $maxValue = $maxValue / 2;
            }

            return $maxValue - 1;
        }

        return null;
    }

    public function null($type = true)
    {
        $this->allowNull($type);

        return $this;
    }

    public function notNull($type = true)
    {
        $this->allowNull(!$type);

        return $this;
    }

    public function unsigned($type = true)
    {
        $this->isUnsigned($type);

        return $this;
    }

    public function name($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function type($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function length($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function isUnsigned($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function allowNull($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function def($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchScalarVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function comment($value = null, $type = Obj::VAR_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, func_get_args());
    }

    public function values($key = null, $value = null, $type = Obj::VAR_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, func_get_args());
    }
}
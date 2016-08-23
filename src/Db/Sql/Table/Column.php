<?php

namespace Greg\Db\Sql\Table;

use Greg\Support\Arr;

class Column
{
    const TYPE_TINYINT = 'tinyint';

    const TYPE_SMALLINT = 'smallint';

    const TYPE_MEDIUMINT = 'mediumint';

    const TYPE_INT = 'int';

    const TYPE_BIGINT = 'bigint';

    const TYPE_DOUBLE = 'double';

    const TYPE_VARCHAR = 'varchar';

    const TYPE_TEXT = 'text';

    const TYPE_DATE = 'date';

    const TYPE_TIME = 'time';

    const TYPE_DATETIME = 'datetime';

    const TYPE_TIMESTAMP = 'timestamp';

    const CURRENT_TIMESTAMP = 'now';

    const TINYINT_LENGTH = 1;

    const SMALLINT_LENGTH = 2;

    const MEDIUMINT_LENGTH = 3;

    const INT_LENGTH = 4;

    const BIGINT_LENGTH = 8;

    const DOUBLE_LENGTH = 8;

    const INT_TYPES = [
        self::TYPE_TINYINT => self::TINYINT_LENGTH,
        self::TYPE_SMALLINT => self::SMALLINT_LENGTH,
        self::TYPE_MEDIUMINT => self::MEDIUMINT_LENGTH,
        self::TYPE_INT => self::INT_LENGTH,
        self::TYPE_BIGINT => self::BIGINT_LENGTH,
    ];

    const FLOAT_TYPES = [
        self::TYPE_DOUBLE => self::DOUBLE_LENGTH,
    ];

    protected $name = null;

    protected $type = null;

    protected $length = null;

    protected $isUnsigned = false;

    protected $allowNull = true;

    protected $defaultValue = null;

    protected $comment = null;

    protected $values = [];

    static public function getIntLength($type)
    {
        return Arr::get(static::INT_TYPES, $type);
    }

    static public function getFloatLength($type)
    {
        return Arr::get(static::FLOAT_TYPES, $type);
    }

    static public function isIntType($type)
    {
        return static::getIntLength($type) !== null;
    }

    static public function isFloatType($type)
    {
        return static::getFloatLength($type) !== null;
    }

    static public function isNumericType($type)
    {
        return static::isIntType($type) || static::isFloatType($type);
    }

    public function isInt()
    {
        return $this->isIntType($this->type());
    }

    public function isFloat()
    {
        return $this->isFloatType($this->type());
    }

    public function isNumeric()
    {
        return $this->isInt() || $this->isFloat();
    }

    public function getMinValue()
    {
        if ($this->isNumeric()) {
            if ($this->unsigned()) {
                return 0;
            }

            return ($this->getMaxValue() + 1) * -1;
        }

        return null;
    }

    public function getMaxValue()
    {
        if ($len = $this->getIntLength($this->type())) {
            $maxValue = 16 ** ($len * 2);

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

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function type($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function length($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function isUnsigned($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function allowNull($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function defaultValue($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchScalarVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function comment($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function values($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
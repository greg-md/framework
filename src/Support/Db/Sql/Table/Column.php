<?php

namespace Greg\Support\Db\Sql\Table;

use Greg\Support\Tool\Arr;
use Greg\Support\Tool\Obj;

class Column
{
    const TYPE_TINYINT = 'tinyint';

    const TYPE_SMALLINT = 'smallint';

    const TYPE_MEDIUMINT = 'mediumint';

    const TYPE_INT = 'int';

    const TYPE_BIGINT = 'bigint';

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

    const NUMERIC_TYPES = [
        self::TYPE_TINYINT => self::TINYINT_LENGTH,
        self::TYPE_SMALLINT => self::SMALLINT_LENGTH,
        self::TYPE_MEDIUMINT => self::MEDIUMINT_LENGTH,
        self::TYPE_INT => self::INT_LENGTH,
        self::TYPE_BIGINT => self::BIGINT_LENGTH,
    ];

    protected $name = null;

    protected $type = self::TYPE_INT;

    protected $length = null;

    protected $isUnsigned = false;

    protected $allowNull = true;

    protected $def = null;

    protected $comment = null;

    protected $values = [];

    //protected $autoIncrement = false;

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
        // phpStorm bug fix
        $types = static::NUMERIC_TYPES;

        return Arr::get($types, $type);
    }

    static public function isNumericType($type)
    {
        return static::getNumericLength($type) !== null;
    }

    public function isNumeric()
    {
        return $this->isNumericType($this->type());
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
        if ($len = $this->getNumericLength($this->type())) {
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

    public function def($value = null, $type = Obj::PROP_REPLACE)
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
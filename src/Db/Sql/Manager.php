<?php

namespace Greg\Db\Sql;

use Greg\Engine\Adapter;
use Greg\Engine\Internal;
use Greg\Support\Obj;

/**
 * Class Manager
 * @package Greg\Db\Sql
 *
 * @method beginTransaction();
 * @method commit();
 * @method errorCode();
 * @method errorInfo();
 * @method exec($query);
 * @method getAttribute($name);
 * @method inTransaction();
 * @method lastInsertId($name = null);
 * @method Storage\Adapter\StmtInterface prepare($query);
 * @method query($query, $mode = null, $_ = null);
 * @method quote($string, $type = Storage::PARAM_STR);
 * @method rollBack();
 * @method setAttribute($name, $value);
 */
class Manager
{
    use Adapter, Internal;

    public function __construct($adapter)
    {
        $this->adapter($adapter);

        return $this;
    }
}
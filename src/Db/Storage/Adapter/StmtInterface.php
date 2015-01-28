<?php

namespace Greg\Db\Storage\Adapter;

use Greg\Db\Storage;

interface StmtInterface
{
    public function bindColumn($column, &$param, $type = null, $length = null, $options = null);

    public function bindParam($param, &$var, $type = Storage::PARAM_STR, $length = null, $options = null);

    public function bindValue($param, $value, $type = Storage::PARAM_STR);

    public function closeCursor();

    public function columnCount();

    public function debugDumpParams();

    public function errorCode();

    public function errorInfo();

    public function execute($params = []);

    public function fetch($style = null, $orientation = Storage::FETCH_ORI_NEXT, $offset = 0);

    public function fetchAll($style = null, $argument = null, $args = []);

    public function fetchColumn($column = 0);

    public function fetchOne($column = 0);

    public function fetchObject($class = 'stdClass', $args = []);

    public function getAttribute($name);

    public function getColumnMeta($column);

    public function nextRowset();

    public function rowCount();

    public function setAttribute($name, $value);

    public function setFetchMode($mode, $_ = null);
}
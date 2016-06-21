<?php

namespace Greg\Db\Sql\Storage;

use Greg\Db\Sql\Storage\Adapter\AdapterInterface;
use Greg\Db\Sql\Storage\Mysql\Query\Delete;
use Greg\Db\Sql\Storage\Mysql\Query\Insert;
use Greg\Db\Sql\Storage\Mysql\Query\Select;
use Greg\Db\Sql\Storage\Mysql\Query\Update;
use Greg\Db\Sql\StorageInterface;
use Greg\Db\Sql\Table\Column;
use Greg\Engine\InternalTrait;
use Greg\Tool\Arr;
use Greg\Tool\Obj;
use Greg\Tool\Str;

class Mysql implements StorageInterface
{
    use InternalTrait;

    protected $dns = null;

    protected $dbName = null;

    protected $username = null;

    protected $password = null;

    protected $options = [];

    protected $adapter = Mysql\Adapter\Pdo::class;

    public function __construct($dns, $username = null, $password = null, array $options = [])
    {
        $this->dns($dns);

        $dnsInfo = Str::parse($dns, ';');

        $this->dbName(Arr::get($dnsInfo, 'dbname'));

        $this->username($username);

        $this->password($password);

        $this->options($options);

        return $this;
    }

    public function getTableSchema($tableName)
    {
        $info = $this->getTableInfo($tableName);

        $references = $this->getTableReferences($tableName);

        $relationships = $this->getTableRelationships($tableName);

        return [
            'info' => $info,
            'references' => $references,
            'relationships' => $relationships,
        ];
    }

    public function getTableInfo($tableName)
    {
        $stmt = $this->query('Describe `' . $tableName . '`');

        $columnsInfo = $stmt->fetchAssocAll();

        $primaryKeys = [];

        $autoIncrement = null;

        $columns = [];

        foreach($columnsInfo as $columnInfo) {
            if ($columnInfo['Key'] == 'PRI') {
                $primaryKeys[] = $columnInfo['Field'];
            }

            if ($columnInfo['Extra'] == 'auto_increment') {
                $autoIncrement = $columnInfo['Field'];
            }

            $columns[] = $this->parseColumnInfo($columnInfo);
        }

        return [
            'columns' => $columns,
            'primaryKeys' => $primaryKeys,
            'autoIncrement' => $autoIncrement,
        ];
    }

    public function parseColumnInfo($columnInfo)
    {
        $info = $this->parseColumnInfoAsArray($columnInfo);

        $column = new Column();

        $column->name($info['name']);

        $column->type($info['type']);

        $column->length($info['length']);

        $column->unsigned($info['unsigned']);

        $column->null($info['null']);

        $column->defaultValue($info['defaultValue']);

        $column->comment($info['comment']);

        $column->values($info['values']);

        return $column;
    }

    protected function parseColumnInfoAsArray($columnInfo)
    {
        $name = $columnInfo['Field'];

        $type = null;

        $length = null;

        $unsigned = false;

        $null = true;

        $defaultValue = null;

        $comment = null;

        $values = [];

        if (preg_match('#^([a-z]+)(?:\((.+?)\))?(?: (unsigned))?#i', $columnInfo['Type'], $matches)) {
            $type = $matches[1];

            if (Arr::hasRef($matches, 2)) {
                if ($matches[1] === 'enum') {
                    $values = str_getcsv($matches[2], ',', "'");
                } else {
                    $length = $matches[2];
                }
            }

            if (Arr::hasRef($matches, 3)) {
                $unsigned = true;
            }

            if ($matches[1] === 'text') {
                $length = 65535;
            }
        }

        if ($columnInfo['Null'] == 'NO') {
            $null = false;
        }

        if ($columnInfo['Default'] === '') {
            $columnInfo['Default'] = null;
        }

        if (!$null) {
            $columnInfo['Default'] = (string)$columnInfo['Default'];
        }

        if (Column::isNumericType($type) and (!$null or $columnInfo['Default'] !== null)) {
            $columnInfo['Default'] = (int)$columnInfo['Default'];
        }

        $defaultValue = $columnInfo['Default'];

        return compact('name', 'type', 'length', 'unsigned', 'null', 'defaultValue', 'comment', 'values');
    }

    public function getTableReferences($tableName)
    {
        $stmt = $this->query('SHOW CREATE TABLE `' . $tableName . '`');

        $sql = $stmt->fetchOne('Create Table');

        $regex = 'CONSTRAINT `(.+)` FOREIGN KEY \((.+)\) REFERENCES `(.+)` \((.+)\) ON DELETE (.+) ON UPDATE (.+)';

        $references = [];

        if (preg_match_all('#' . $regex . '#i', $sql, $matches)) {
            $dbName = $this->dbName();

            foreach($matches[0] as $k => $match) {
                $constraint = [];

                $columnsNames = Str::splitQuoted($matches[2][$k], ', ', '`');

                $referencesColumnsNames = Str::splitQuoted($matches[4][$k], ', ', '`');

                foreach($columnsNames as $kk => $columnName) {
                    $constraint[$kk + 1] = [
                        'Position' => $kk + 1,
                        'ColumnName' => $columnName,
                        'ReferencedColumnName' => $referencesColumnsNames[$kk],
                    ];
                }

                $references[] = [
                    'ConstraintName' => $matches[1][$k],
                    'DbName' => $dbName,
                    'TableName' => $tableName,
                    'ReferencedTableSchema' => $dbName,
                    'ReferencedTableName' => $matches[3][$k],
                    'OnUpdate' => $matches[5][$k],
                    'OnDelete' => $matches[6][$k],
                    'Constraint' => $constraint,
                ];
            }
        }

        return $references;
    }

    public function getTableRelationships($tableName, $rules = false)
    {
        $query = $this->select()
            ->from(['KCU' => 'information_schema.KEY_COLUMN_USAGE'], [
                'TABLE_SCHEMA',
                'TABLE_NAME',
                'COLUMN_NAME',
                'CONSTRAINT_NAME',
                'ORDINAL_POSITION',
                'POSITION_IN_UNIQUE_CONSTRAINT',
                'REFERENCED_TABLE_SCHEMA',
                'REFERENCED_TABLE_NAME',
                'REFERENCED_COLUMN_NAME',
            ])
            ->from(['TC' => 'information_schema.TABLE_CONSTRAINTS'], 'CONSTRAINT_TYPE')
            ->where('KCU.TABLE_SCHEMA = TC.TABLE_SCHEMA')
            ->where('KCU.TABLE_NAME = TC.TABLE_NAME')
            ->where('KCU.CONSTRAINT_NAME = TC.CONSTRAINT_NAME')

            ->order('KCU.TABLE_SCHEMA')
            ->order('KCU.TABLE_NAME')
            ->order('KCU.CONSTRAINT_NAME')
            ->order('KCU.ORDINAL_POSITION')
            ->order('KCU.POSITION_IN_UNIQUE_CONSTRAINT');

        if ($rules) {
            $query->from(['RC' => 'information_schema.REFERENTIAL_CONSTRAINTS'], [
                    'UPDATE_RULE',
                    'DELETE_RULE',
                ])
                ->where('KCU.CONSTRAINT_SCHEMA = RC.CONSTRAINT_SCHEMA')
                ->where('KCU.CONSTRAINT_NAME = RC.CONSTRAINT_NAME');
        }

        $query->where('TC.CONSTRAINT_TYPE = "FOREIGN KEY"');

        $query->whereCol('KCU.REFERENCED_TABLE_SCHEMA', $this->dbName());

        $query->whereCol('KCU.REFERENCED_TABLE_NAME', $tableName);

        $items = $query->assocAll();

        $relationships = [];

        foreach($items as $item) {
            if (!isset($relationships[$item['CONSTRAINT_NAME']])) {
                $relationships[$item['CONSTRAINT_NAME']] = [
                    'ConstraintName' => $item['CONSTRAINT_NAME'],
                    'DbName' => $item['REFERENCED_TABLE_SCHEMA'],
                    'TableName' => $item['REFERENCED_TABLE_NAME'],
                    'RelationshipTableSchema' => $item['TABLE_SCHEMA'],
                    'RelationshipTableName' => $item['TABLE_NAME'],
                    //'OnUpdate' => $item['UPDATE_RULE'],
                    //'OnDelete' => $item['DELETE_RULE'],
                ];

                if ($rules) {
                    $relationships[$item['CONSTRAINT_NAME']]['OnUpdate'] = $item['UPDATE_RULE'];
                    $relationships[$item['CONSTRAINT_NAME']]['OnDelete'] = $item['DELETE_RULE'];
                }
            }

            $relationships[$item['CONSTRAINT_NAME']]['Constraint'][$item['POSITION_IN_UNIQUE_CONSTRAINT']] = [
                'Position' => $item['POSITION_IN_UNIQUE_CONSTRAINT'],
                'ColumnName' => $item['REFERENCED_COLUMN_NAME'],
                'RelationshipColumnName' => $item['COLUMN_NAME'],
            ];
        }

        return $relationships;
    }

    /**
     * @param null $columns
     * @param null $_
     * @return Select
     * @throws \Exception
     */
    public function select($columns = null, $_ = null)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }

        $query = $this->newSelect();

        if ($columns) {
            $query->columns($columns);
        }

        return $query;
    }

    protected function newSelect()
    {
        return new Select($this);
    }

    /**
     * @param null $into
     * @return Insert
     * @throws \Exception
     */
    public function insert($into = null)
    {
        $query = $this->newInsert();

        if ($into !== null) {
            $query->into($into);
        }

        return $query;
    }

    protected function newInsert()
    {
        return new Insert($this);
    }

    /**
     * @param null $from
     * @param bool $delete
     * @return Delete
     */
    public function delete($from = null, $delete = false)
    {
        $query = $this->newDelete();

        if ($from !== null) {
            $query->from($from, $delete);
        }

        return $query;
    }

    protected function newDelete()
    {
        return new Delete($this);
    }

    /**
     * @param null $table
     * @return Update
     * @throws \Exception
     */
    public function update($table = null)
    {
        $query = $this->newUpdate();

        if ($table !== null) {
            $query->table($table);
        }

        return $query;
    }

    protected function newUpdate()
    {
        return new Update($this);
    }

    public function beginTransaction()
    {
        return $this->adapter()->beginTransaction();
    }

    public function commit()
    {
        return $this->adapter()->commit();
    }

    public function errorCode()
    {
        return $this->adapter()->errorCode();
    }

    public function errorInfo()
    {
        return $this->adapter()->errorInfo();
    }

    public function exec($query)
    {
        return $this->adapter()->exec($query);
    }

    public function getAttribute($name)
    {
        return $this->adapter()->getAttribute($name);
    }

    public function inTransaction()
    {
        return $this->adapter()->inTransaction();
    }

    public function lastInsertId($name = null)
    {
        return $this->adapter()->lastInsertId($name);
    }

    /**
     * @param $query
     * @param array $options
     * @return Adapter\StmtInterface
     */
    public function prepare($query, $options = [])
    {
        return $this->adapter()->prepare($query, $options = []);
    }

    /**
     * @param $query
     * @param null $mode
     * @param null $_
     * @return Adapter\StmtInterface
     */
    public function query($query, $mode = null, $_ = null)
    {
        return $this->adapter()->query(...func_get_args());
    }

    public function quote($string, $type = self::PARAM_STR)
    {
        return $this->adapter()->quote($string, $type);
    }

    public function rollBack()
    {
        return $this->adapter()->rollBack();
    }

    public function setAttribute($name, $value)
    {
        return $this->adapter()->setAttribute($name, $value);
    }

    public function dns($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function dbName($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function username($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function password($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function options($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = true)
    {
        return Obj::fetchArrayReplaceVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param AdapterInterface $value
     * @return AdapterInterface|null
     */
    public function adapter(AdapterInterface $value = null)
    {
        return Obj::fetchCallableVar($this, $this->{__FUNCTION__},function($adapter) {
            if (!is_object($adapter)) {
                $adapter = $this->loadClassInstance($adapter, $this->dns(), $this->username(), $this->password(), $this->options());
            }

            return $adapter;
        }, ...func_get_args());
    }

    public function __call($method, array $args = [])
    {
        return $this->adapter()->$method(...$args);
    }
}
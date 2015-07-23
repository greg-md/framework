<?php

namespace Greg\Db\Sql;

use Greg\Cache\StorageInterface as CacheStorageInterface;
use Greg\Db\Sql\Query\Expr;
use Greg\Db\Sql\Query\Where;
use Greg\Db\Sql\Table\Column;
use Greg\Db\Sql\Table\Row;
use Greg\Support\Engine\InternalTrait;
use Greg\Support\Arr;
use Greg\Support\DateTime;
use Greg\Support\Obj;
use Greg\Support\Url;

/**
 * Class Table
 * @package Greg\Db\Sql
 *
 * @method beginTransaction()
 * @method commit()
 * @method rollBack()
 * @method lastInsertId($name = null)
 */
class Table
{
    use InternalTrait;

    protected $prefix = null;

    protected $name = null;

    protected $alias = null;

    protected $columns = [];

    protected $columnsTypes = [];

    protected $autoIncrement = null;

    protected $primary = [];

    protected $unique = [];

    protected $references = [];

    protected $relationships = [];

    protected $dependencies = [];

    protected $relationshipsAliases = [];

    protected $referencesAliases = [];

    protected $rowClass = Table\Row::class;

    protected $rowFullClass = Table\RowFull::class;

    protected $rowsClass = Table\Rows::class;

    protected $rowsPaginationClass = Table\RowsPagination::class;

    protected $scaffolding = [];

    protected $nameColumn = null;

    protected $label = null;

    protected $storage = null;

    protected $cacheStorage = null;

    protected $autoLoadSchema = false;

    protected $autoLoadInfo = true;

    protected $autoLoadReferences = true;

    /**
     * If you want to enable it, make sure this table have cache storage, because the queries are slow.
     * @var bool
     */
    protected $autoLoadRelationships = false;

    protected $cacheSchema = true;

    protected $cacheSchemaLifetime = 0;

    protected $relationshipsTables = [];

    public function init()
    {
        if ($this->autoLoadSchema()) {
            if ($this->autoLoadInfo()) {
                $info = $this->getInfo();

                $info['columns'] && $this->columns($info['columns']);

                $info['primary'] && $this->primary($info['primary']);

                $info['autoIncrement'] && $this->autoIncrement($info['autoIncrement']);
            }

            if ($this->autoLoadReferences()) {
                $this->references($references = $this->getReferences());
            }

            if ($this->autoLoadRelationships()) {
                $this->relationships($relationships = $this->getRelationships());
            }
        }

        if (method_exists($this, 'loadSchema')) {
            $this->loadSchema();
        }

        return $this;
    }

    protected function getInfo()
    {
        $caller = function() {
            return $this->storage()->getTableInfo($this->getName());
        };

        if ($this->cacheSchema() and ($cache = $this->cacheStorage())) {
            $info = $cache->fetch('table_info:' . $this->getName(), $caller, $this->cacheSchemaLifetime());
        } else {
            $info = $caller();
        }

        return $info;
    }

    protected function getReferences()
    {
        $caller = function() {
            return $this->storage()->getTableReferences($this->getName());
        };

        if ($this->cacheSchema() and ($cache = $this->cacheStorage())) {
            $info = $cache->fetch('table_references:' . $this->getName(), $caller, $this->cacheSchemaLifetime());
        } else {
            $info = $caller();
        }

        return $info;
    }

    protected function getRelationships()
    {
        $caller = function() {
            return $this->storage()->getTableRelationships($this->getName());
        };

        if ($this->cacheSchema() and ($cache = $this->cacheStorage())) {
            $info = $cache->fetch('table_relationships:' . $this->getName(), $caller, $this->cacheSchemaLifetime());
        } else {
            $info = $caller();
        }

        return $info;
    }

    /**
     * @param Table\Column[] $columns
     * @return $this
     */
    public function addColumns(array $columns)
    {
        foreach($columns as $column) {
            $this->columns($column->name(), $column);
        }

        return $this;
    }

    public function select($columns = null, $_ = null)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }

        return $this->storage()->select($columns)->table($this)->from($this);
    }

    public function insert(array $data = [])
    {
        return $this->storage()->insert($this)->data($data);
    }

    public function insertData(array $data = [])
    {
        $this->insert($data)->exec();

        return $this;
    }

    public function insertDataFull(array $data = [], $relationships = [])
    {
        $row = $this->newRow($data);

        $row->save();

        $relationships && $this->insertDataRelationships($row, $relationships);

        return $this;
    }

    public function insertDataRelationships(Row $row, $relationships = [])
    {
        foreach($relationships as $relationshipPath => $inserts) {
            list($tableName, $constraintColumns) = explode(':', $relationshipPath, 2);

            $constraintColumns = explode('.', $constraintColumns);

            sort($constraintColumns);

            $table = $this->getRelationshipTable($tableName);

            foreach($this->getTablesRelationships($tableName) as $relationshipInfo) {
                $relationshipColumns = [];

                $relationshipInsert = [];

                foreach($relationshipInfo['Constraint'] as $constraint) {
                    $relationshipColumns[] = $constraint['RelationshipColumnName'];

                    $relationshipInsert[$constraint['RelationshipColumnName']] = $row[$constraint['ColumnName']];
                }

                sort($relationshipColumns);

                if ($constraintColumns != $relationshipColumns) {
                    continue;
                }

                foreach($inserts as $insert) {
                    $table->insertData($relationshipInsert + $insert);
                }

                break;
            }
        }

        return $this;
    }

    public function update(array $values = [])
    {
        $query = $this->storage()->update($this);

        if ($values) {
            $query->set($values);
        }

        return $query;
    }

    public function delete(array $whereIs = [])
    {
        $query = $this->storage()->delete($this, true);

        if ($whereIs) {
            $query->whereCols($whereIs);
        }

        return $query;
    }

    public function exists($column, $value)
    {
        return $this->select($this->storage()->expr(1))->whereCol($column, $value)->exists();
    }

    public function newRow(array $data = [])
    {
        $row = $this->createRow($data);

        $row->isNew(true);

        return $row;
    }

    /**
     * @param array $data
     * @param bool $reset
     * @return \Greg\Db\Sql\Table\Row
     * @throws \Exception
     */
    public function createRow(array $data, $reset = true)
    {
        if (!($class = $this->rowClass())) {
            throw new \Exception('Undefined table row class.');
        }

        if ($reset) {
            $rowData = [];

            foreach($this->columns() as $name => $column) {
                $rowData[$column->name()] = $column->def();
            }

            $data = array_merge($rowData, $data);
        }

        return $this->app()->binder()->loadInstance($class, $this, $data);
    }

    public function createRowFull(array $data)
    {
        if (!($class = $this->rowFullClass())) {
            throw new \Exception('Undefined table row full class.');
        }

        return $this->app()->binder()->loadInstance($class, $this, $data);
    }

    /**
     * @param $data
     * @return \Greg\Db\Sql\Table\Row[]|\Greg\Db\Sql\Table\Rows
     * @throws \Exception
     */
    public function createRows(array $data = [])
    {
        $class = $this->rowsClass();

        if (!$class) {
            throw new \Exception('Undefined table row set class.');
        }

        return $this->app()->binder()->loadInstance($class, $this, $data);
    }

    /**
     * @param $items
     * @param $total
     * @param $page
     * @param $limit
     * @return \Greg\Db\Sql\Table\Row[]|\Greg\Db\Sql\Table\RowsPagination
     * @throws \Exception
     */
    public function createRowsPagination($items, $total, $page = null, $limit = null)
    {
        $class = $this->rowsPaginationClass();

        if (!$class) {
            throw new \Exception('Undefined table row set pagination class.');
        }

        return $this->app()->binder()->loadInstance($class, $this, $items, $total, $page, $limit);
    }

    public function addFullInfo(&$items, $references = null, $relationships = null, $dependencies = '*', $rows = false)
    {
        $this->toFullFormat($items, $rows);

        if ($dependencies) {
            $this->addDependencies($items, $dependencies, $rows);
        }

        if ($references) {
            $this->addReferences($items, $references, $rows);
        }

        if ($relationships) {
            $this->addRelationships($items, $relationships, $rows);
        }

        return $this;
    }

    public function addReferences(&$items, $references, $rows = false)
    {
        if ($references == '*') {
            $references = array_keys($this->references());
        }

        $references = Arr::bring($references);

        $tablesReferences = Arr::group($this->references(), 'ReferencedTableName', false);

        if ($references) {
            foreach($items as &$item) {
                $item['references'] = [];
            }
            unset($item);
        }

        foreach($references as $referenceTableName => $referenceParams) {
            if (is_int($referenceTableName)) {
                $referenceTableName = $referenceParams;

                $referenceParams = [];
            }

            $referenceParams = array_merge([
                'references' => null,
                'relationships' => null,
                'dependencies' => '*',
                'full' => true,
                'callback' => null,
            ], $referenceParams);

            $referenceTable = $this->getRelationshipTable($referenceTableName);

            $tableReferences = $tablesReferences[$referenceTable->getName()];

            if (!$tableReferences) {
                throw new \Exception('Reference `' . $referenceTableName . '` not found.');
            }

            foreach($tableReferences as $info) {
                $columns = [];

                foreach($info['Constraint'] as $constraint) {
                    $columns[] = $constraint['ColumnName'];
                }

                foreach($items as &$item) {
                    $item['references'][implode('.', $columns)] = null;
                }
            }
            unset($item);

            $parts = [];

            foreach($tableReferences as $info) {
                $columns = [];

                foreach($info['Constraint'] as $constraint) {
                    $columns[] = $constraint['ReferencedColumnName'];
                }

                $columnsCount = sizeof($columns);

                $columns = implode('.', $columns);

                if (!isset($parts[$columns])) {
                    $parts[$columns] = [];
                }

                foreach($items as $item) {
                    $itemKeys = [];

                    foreach($info['Constraint'] as $constraint) {
                        $columnName = $constraint['ColumnName'];

                        $key = $item[$this->getName()][$columnName];

                        if (!$key and !$referenceTable->columns($constraint['ReferencedColumnName'])->allowNull()) {
                            continue 2;
                        }

                        if ($key or $columnsCount > 1) {
                            $itemKeys[] = $key;
                        }
                    }

                    if (!array_filter($itemKeys)) {
                        continue;
                    }

                    $hasKeysCombination = false;

                    foreach($parts[$columns] as $keys) {
                        if ($keys === $itemKeys) {
                            $hasKeysCombination = true;
                            break;
                        }
                    }

                    if (!$hasKeysCombination) {
                        $parts[$columns][] = $itemKeys;
                    }
                }
            }

            foreach($parts as $key => $part) {
                if (!$part) {
                    unset($parts[$key]);
                }
            }

            if (!$parts) {
                continue;
            }

            $query = $referenceTable->select();

            if (is_callable($referenceParams['callback'])) {
                $this->app()->binder()->call($referenceParams['callback'], $query);
            }

            $query->where(function(Where $query) use ($parts) {
                foreach($parts as $columns => $values) {
                    $query->orWhereCol(explode('.', $columns), $values);
                }
            });

            if ($referenceParams['full']) {
                $referenceItems = $query->{$rows ? 'rowsFull' : 'assocAllFull'}($referenceParams['references'], $referenceParams['relationships'], $referenceParams['dependencies']);
            } else {
                $referenceItems = $query->{$rows ? 'rows' : 'assocAll'}();
            }

            foreach($tableReferences as $info) {
                $columns = $rColumns = [];

                foreach($info['Constraint'] as $constraint) {
                    $columns[] = $constraint['ColumnName'];

                    $rColumns[] = $constraint['ReferencedColumnName'];
                }

                $key = implode('.', $columns);

                $key = $this->referencesAliases($key) ?: $key;

                foreach($items as &$item) {
                    $values = [];

                    foreach($columns as $column) {
                        $values[] = $item[$this->getName()][$column];
                    }

                    if (array_filter($values)) {
                        foreach($referenceItems as $reItem) {
                            $rValues = [];

                            foreach($rColumns as $rColumn) {
                                if ($referenceParams['full']) {
                                    $rValues[] = $reItem[$referenceTableName][$rColumn];
                                } else {
                                    $rValues[] = $reItem[$rColumn];
                                }
                            }

                            if ($values === $rValues) {
                                $item['references'][$key] = $reItem;
                                break;
                            }
                        }
                    }
                }
                unset($item);
            }
        }

        return $this;
    }

    public function addRelationships(&$items, $relationships, $rows = false)
    {
        if ($relationships == '*') {
            $relationships = array_keys($this->relationships());
        }

        $relationships = Arr::bring($relationships);

        if ($relationships) {
            foreach($items as &$item) {
                $item['relationships'] = [];
            }
            unset($item);
        }

        foreach($relationships as $relationshipTableName => $relationshipParams) {
            if (is_int($relationshipTableName)) {
                $relationshipTableName = $relationshipParams;

                $relationshipParams = [];
            }

            $relationshipParams = array_merge([
                'references' => null,
                'relationships' => null,
                'dependencies' => '*',
                'full' => true,
                'callback' => null,
            ], $relationshipParams);

            $relationshipTable = $this->getRelationshipTable($relationshipTableName);

            $tableRelationships = $this->getTablesRelationships($relationshipTable->getName());

            foreach($items as &$item) {
                foreach($tableRelationships as $info) {
                    $key = [$relationshipTableName];

                    foreach($info['Constraint'] as $constraint) {
                        $key[] = $constraint['RelationshipColumnName'];
                    }

                    $key = implode('.', $key);

                    $item['relationships'][$this->relationshipsAliases($key) ?: $key] = $rows ? $relationshipTable->createRows() : [];
                }
            }
            unset($item);

            $query = $relationshipTable->select();

            if (is_callable($relationshipParams['callback'])) {
                $this->app()->binder()->call($relationshipParams['callback'], $query);
            }

            if ($query->limit()) {
                dd('under construction references with limit');
                /*
                $queries = [];

                foreach($items as $item) {
                    $parts = [];
                    foreach($tableRelationships as $info) {
                        $itemKeys = [];
                        foreach($info['Constraint'] as $constraint) {
                            $key = $item[$modelName][$constraint['ColumnName']];
                            if ($key) {
                                $itemKeys[] = $key;
                            }
                        }
                        if (!array_filter($itemKeys)) {
                            continue;
                        }
                        $columns = [];
                        foreach($info['Constraint'] as $constraint) {
                            $columns[] = $constraint['RelationshipColumnName'];
                        }
                        $columns = implode('.', $columns);
                        $parts[$columns] = $itemKeys;
                    }

                    $q = clone $query;
                    foreach($parts as $columns => $values) {
                        $q->whereRow(explode('.', $columns), $values);
                    }
                    $queries[] = $q;
                }

                $query = $this->select()->union($queries);
                */
            } else {
                $parts = [];

                foreach($tableRelationships as $info) {
                    $columns = [];

                    foreach($info['Constraint'] as $constraint) {
                        $columns[] = $constraint['RelationshipColumnName'];
                    }

                    $columns = implode('.', $columns);

                    if (!isset($parts[$columns])) {
                        $parts[$columns] = [];
                    }

                    foreach($items as $item) {
                        $itemKeys = [];

                        foreach($info['Constraint'] as $constraint) {
                            $key = $item[$this->getName()][$constraint['ColumnName']];

                            if ($key) {
                                $itemKeys[] = $key;
                            }
                        }

                        if (!array_filter($itemKeys)) {
                            continue;
                        }

                        $hasKeysCombination = false;

                        foreach($parts[$columns] as $keys) {
                            if ($keys === $itemKeys) {
                                $hasKeysCombination = true;
                                break;
                            }
                        }

                        if (!$hasKeysCombination) {
                            $parts[$columns][] = $itemKeys;
                        }
                    }
                }

                foreach($parts as $key => $part) {
                    if (!$part) {
                        unset($parts[$key]);
                    }
                }

                if (!$parts) {
                    continue;
                }

                $query->where(function(Where $query) use ($parts) {
                    foreach($parts as $columns => $values) {
                        $query->orWhereCol(explode('.', $columns), $values);
                    }
                });
            }

            if ($relationshipParams['full']) {
                $relationshipItems = $query->{$rows ? 'rowsFull' : 'assocAllFull'}($relationshipParams['references'], $relationshipParams['relationships'], $relationshipParams['dependencies']);
            } else {
                $relationshipItems = $query->{$rows ? 'rows' : 'assocAll'}();
            }

            foreach($tableRelationships as $info) {
                $columns = $relationshipColumns = [];

                foreach($info['Constraint'] as $constraint) {
                    $columns[] = $constraint['ColumnName'];

                    $relationshipColumns[] = $constraint['RelationshipColumnName'];
                }

                $key = $relationshipColumns;

                Arr::prepend($key, $relationshipTableName);

                $key = implode('.', $key);

                $key = $this->relationshipsAliases($key) ?: $key;

                foreach($items as &$item) {
                    $values = [];

                    foreach($columns as $column) {
                        $values[] = $item[$this->getName()][$column];
                    }

                    if (array_filter($values)) {
                        foreach($relationshipItems as $relationshipItem) {
                            $relationshipValues = [];

                            foreach($relationshipColumns as $relationshipColumn) {
                                if ($relationshipParams['full']) {
                                    $relationshipValues[] = $relationshipItem[$relationshipTableName][$relationshipColumn];
                                } else {
                                    $relationshipValues[] = $relationshipItem[$relationshipColumn];
                                }
                            }

                            if ($values === $relationshipValues) {
                                $item['relationships'][$key][] = $relationshipItem;
                            }
                        }
                    }
                }
                unset($item);
            }
        }

        return $this;
    }

    public function addDependencies(&$items, $dependencies, $rows = false)
    {
        if ($dependencies == '*') {
            $dependencies = array_keys($this->dependencies());
        }

        $dependencies = Arr::bring($dependencies);

        foreach($dependencies as $dependenceName => $dependenceParams) {
            if (is_int($dependenceName)) {
                $dependenceName = $dependenceParams;

                $dependenceParams = [];
            }

            foreach($items as $key => $item) {
                $items[$key][$dependenceName] = null;
            }

            $dependenceInfo = $this->dependencies($dependenceName);

            /* @var $dependenceTable string|Table */
            $dependenceTable = $dependenceInfo['table'];

            $dependenceTable = $dependenceTable::newInstance($this->appName(), $this->storage());

            $tableRelationships = $this->getTablesRelationships($dependenceTable->name());

            $parts = [];

            foreach($tableRelationships as $tablesRelationships) {
                $columns = [];

                foreach($tablesRelationships['Constraint'] as $constraint) {
                    $columns[] = $constraint['RelationshipColumnName'];
                }

                $columns = implode('.', $columns);

                if (!isset($parts[$columns])) {
                    $parts[$columns] = [];
                }

                foreach($items as $item) {
                    $itemKeys = [];

                    foreach($tablesRelationships['Constraint'] as $constraint) {
                        $key = $item[$this->name()][$constraint['ColumnName']];

                        if ($key) {
                            $itemKeys[] = $key;
                        }
                    }

                    if (!array_filter($itemKeys)) {
                        continue;
                    }

                    $hasKeysCombination = false;

                    foreach($parts[$columns] as $keys) {
                        if ($keys === $itemKeys) {
                            $hasKeysCombination = true;

                            break;
                        }
                    }

                    if (!$hasKeysCombination) {
                        $parts[$columns][] = $itemKeys;
                    }
                }
            }

            foreach($parts as $key => $part) {
                if (!$part) {
                    unset($parts[$key]);
                }
            }

            if (!$parts) {
                continue;
            }

            $query = $dependenceTable->select();

            if ($filter = $dependenceInfo['filter']) {
                $query->whereCols($filter);
            }

            // make next query in closure
            $query->where(function($query) use ($parts) {

            });

            /*
            $k = 1;

            $pCount = sizeof($parts);

            foreach($parts as $columns => $values) {
                $columns = explode('.', $columns);

                if ($k == 1) {
                    if ($pCount > 1) {
                        $query->whereRowBO($columns, $values);
                    } else {
                        $query->whereRow($columns, $values);
                    }
                } else {
                    if ($k == $pCount) {
                        $query->orWhereRowBC($columns, $values);
                    } else {
                        $query->orWhereRow($columns, $values);
                    }
                }
                ++$k;
            }

            $dependenceParams = $this->_toArray($dependenceParams);
            $dependenceParams->replacePrepend(array(
                'fetchType' => $fetchType,
                'fetchFull' => true,
                'depends' => '*',
                'callback' => null,
            ));
            $callback = $dependenceParams['callback'];
            if (($callback instanceof Closure)) {
                $callback($query);
            }
            $dependFetchType = $dependenceParams['fetchType'];
            if ($dependFetchType == self::FETCH_ROWSET_OBJECT) {
                $dependFetchType = self::FETCH_ROW_OBJECT;
            }
            $fetchFull = $dependenceParams['fetchFull'];
            if ($fetchFull) {
                $reItems = $dependenceTable->fetchRowsFull($query,
                    $dependenceParams['references'], $dependenceParams['relationships'], $dependenceParams['depends'],
                    $dependFetchType, $dependenceParams['rowClass'], $dependenceParams['rowFullClass']);
            } else {
                $reItems = $dependenceTable->fetchRows($query, $dependFetchType, $dependenceParams['rowClass']);
            }

            foreach($tableRelationships as $tablesRelationships) {
                $columns = $columns = [];
                foreach($tablesRelationships['Constraint'] as $constraint) {
                    $columns[] = $constraint['ColumnName'];
                    $columns[] = $constraint['RelationshipColumnName'];
                }
                foreach($items as &$item) {
                    $values = [];
                    foreach($columns as $column) {
                        $values[] = $item[$modelName][$column];
                    }
                    if (array_filter($values)) {
                        foreach($reItems as $reItem) {
                            $rValues = [];
                            foreach($columns as $rColumn) {
                                $rValues[] = $fetchFull ? $reItem[$depModelName][$rColumn] : $reItem[$rColumn];
                            }
                            if ($values === $rValues) {
                                $item[$dependenceName] = $reItem;
                            }
                        }
                    }
                }
                unset($item);
            }
            */
        }

        return $this;
    }

    public function toFullFormat(&$items, $rows = false)
    {
        foreach($items as $key => &$item) {
            if ($rows) {
                $item = $this->createRow($item);
            }

            $item = [
                $this->getName() => $item,
            ];

            if ($rows) {
                $item = $this->createRowFull($item);
            }
        }
        unset($item);

        return $this;
    }

    public function parseData(array $data)
    {
        foreach($data as $columnName => &$value) {
            if (!($column = $this->columns($columnName))) {
                unset($data[$columnName]);

                continue;
            }

            if (($value instanceof Expr)) {
                continue;
            }

            if ($value === '') {
                $value = null;
            }

            if (!$column->allowNull()) {
                $value = (string)$value;
            }

            if ($column->isNumeric() and (!$column->allowNull() or $value !== null)) {
                $value = (int)$value;
            }

            switch($this->columnsTypes($columnName) ?: $column->type()) {
                case Column::TYPE_DATETIME:
                case Column::TYPE_TIMESTAMP:
                    if ($value) {
                        $value = DateTime::formatTimeLocale('%Y-%m-%d %H:%M:%S', strtoupper($value) === 'CURRENT_TIMESTAMP' ? null : $value);
                    }

                    break;
                case Column::TYPE_DATE:
                    if ($value) {
                        $value = DateTime::formatTimeLocale('%Y-%m-%d', $value);
                    }

                    break;
                case Column::TYPE_TIME:
                    if ($value) {
                        $value = DateTime::formatTimeLocale('%H:%M:%S', $value);
                    }

                    break;
                case 'systemName':
                    if ($value) {
                        $value = Url::transform($value);
                    }

                    break;
                case 'boolean':
                    $value = (bool)$value;

                    break;
            }
        }
        unset($value);

        return $data;
    }

    public function getName()
    {
        if (!($name = $this->name())) {
            throw new \Exception('Table name is not defined.');
        }

        return $name;
    }

    public function getTablesRelationships($name = null)
    {
        $tablesRelationships = Arr::group($this->relationships(), 'RelationshipTableName', false);

        if ($name) {
            if (!Arr::has($tablesRelationships, $name)) {
                throw new \Exception('Relationship table `' . $name . '` not found in `' . $this->name() . '`.');
            }

            return $tablesRelationships[$name];
        }

        return $tablesRelationships;
    }

    /**
     * @param $name
     * @return Table
     * @throws \Exception
     */
    public function getRelationshipTable($name)
    {
        $table = $this->relationshipsTables($name);

        if (!$table) {
            throw new \Exception('Relationship table `' . $name . '` not found in table `' . $this->getName() . '`.');
        }

        if (is_callable($table)) {
            $table = $this->app()->binder()->call($table);
        }

        if (!is_object($table)) {
            $table = $table::instance($this->appName());
        }

        return $table;
    }

    public function prefix($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function name($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function alias($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param null $key
     * @param null $value
     * @param string $type
     * @param bool $replace
     * @return $this|Table\Column|Table\Column[]
     */
    public function columns($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function columnsTypes($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function autoIncrement($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function primary($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function unique($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function references($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function relationships($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function dependencies($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function relationshipsAliases($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function referencesAliases($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function rowClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function rowFullClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function rowsClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function rowsPaginationClass($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function scaffolding($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function nameColumn($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function label($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param StorageInterface $value
     * @return StorageInterface|null
     */
    public function storage(StorageInterface $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param CacheStorageInterface $value
     * @return CacheStorageInterface|null
     */
    public function cacheStorage(CacheStorageInterface $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function autoLoadSchema($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function autoLoadInfo($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function autoLoadReferences($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function autoLoadRelationships($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function cacheSchema($value = null)
    {
        return Obj::fetchBoolVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function cacheSchemaLifetime($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function relationshipsTables($key = null, $value = null, $type = Obj::PROP_APPEND, $replace = false)
    {
        return Obj::fetchArrayVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    public function __call($method, $args = [])
    {
        return $this->storage()->{$method}(...$args);
    }
}
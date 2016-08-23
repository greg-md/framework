<?php

namespace Greg\Db\Sql;

use Greg\Db\Sql\Query\Expr;
use Greg\Db\Sql\Table\Column;
use Greg\Db\Sql\Table\TableConstraint;

trait TableTrait
{
    protected $prefix = null;

    protected $name = null;

    protected $alias = null;

    protected $columns = [];

    protected $customColumnsTypes = [];

    protected $autoIncrement = null;

    protected $primaryKeys = [];

    protected $uniqueKeys = [];

    protected $references = [];

    protected $relationships = [];

    protected $dependencies = [];

    protected $relationshipsAliases = [];

    protected $referencesAliases = [];

    protected $nameColumn = null;

    protected $query = null;

    /**
     * @var StorageInterface|null
     */
    protected $storage = null;

    public function getFirstUniqueKeys()
    {
        if ($autoIncrement = $this->getAutoIncrement()) {
            return [$autoIncrement];
        }

        if ($primaryKeys = $this->getPrimaryKeys()) {
            return $primaryKeys;
        }

        if ($uniqueKeys = current($this->getAllUniqueKeys())) {
            return $uniqueKeys;
        }

        return array_keys($this->getColumns());
    }

    public function combineFirstUniqueKeys($values)
    {
        $values = (array)$values;

        if (!$keys = $this->getFirstUniqueKeys()) {
            throw new \Exception('Table does not have primary keys.');
        }

        if (sizeof($keys) !== sizeof($values)) {
            throw new \Exception('Unique columns count should be the same as keys count.');
        }

        return array_combine($keys, $values);
    }

    public function select($columns = null, $_ = null)
    {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }

        $this->query = $this->getStorage()->select($columns)->table($this)->from($this);

        return $this;
    }

    public function update(array $values = [])
    {
        $query = $this->getStorage()->update($this);

        if ($values) {
            $query->set($values);
        }

        return $query;
    }

    public function delete(array $whereIs = [])
    {
        $query = $this->getStorage()->delete($this, true);

        if ($whereIs) {
            $query->whereCols($whereIs);
        }

        return $query;
    }

    public function insert(array $data = [])
    {
        return $this->getStorage()->insert($this)->data($data);
    }

    public function insertData(array $data = [])
    {
        $this->insert($data)->exec();

        return $this;
    }

    public function getPairs(array $whereIs = [], callable $callable = null)
    {
        if (!$columnName = $this->getNameColumn()) {
            throw new \Exception('Undefined column name for table `' . $this->getName() . '`.');
        }

        $query = $this->select();

        $query->columns($query->concat($this->getFirstUniqueKeys(), ':'), $columnName);

        if ($whereIs) {
            $query->whereCols($whereIs);
        }

        if ($callable) {
            $callable($query);
        }

        return $query->pairs();
    }

    public function exists($column, $value)
    {
        return $this->select(new Expr(1))->whereCol($column, $value)->exists();
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($name)
    {
        $this->prefix = (string)$name;

        return $this;
    }

    public function getFullName()
    {
        return $this->getPrefix() . $this->getName();
    }

    public function getName()
    {
        if (!$this->name) {
            throw new \Exception('Table name is not defined.');
        }

        return $this->name;
    }

    public function hasName()
    {
        return $this->name ? true : false;
    }

    public function setName($name)
    {
        $this->name = (string)$name;

        return $this;
    }

    public function getAlias()
    {
        return $this->alias;
    }

    public function setAlias($name)
    {
        $this->alias = (string)$name;

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns(array $columns)
    {
        $this->columns = [];

        foreach($columns as $column) {
            $this->addColumn($column);
        }

        return $this;
    }

    public function addColumn(Column $column)
    {
        $this->columns[] = $column;

        return $this;
    }

    public function getCustomColumnTypes()
    {
        return $this->customColumnsTypes;
    }

    public function setCustomColumnTypes(array $columnsTypes)
    {
        foreach($columnsTypes as $key => $value) {
            $this->setCustomColumnType($key, $value);
        }

        return $this;
    }

    public function setCustomColumnType($key, $value)
    {
        $this->customColumnsTypes[(string)$key] = (string)$value;

        return $this;
    }

    public function getCustomColumnType($key)
    {
        return array_key_exists($key, $this->customColumnsTypes) ? $this->customColumnsTypes[$key] : null;
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement($columnName)
    {
        $this->autoIncrement = (string)$columnName;

        return $this;
    }

    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    public function setPrimaryKeys($columnsNames)
    {
        $this->primaryKeys = (array)$columnsNames;

        return $this;
    }

    public function getAllUniqueKeys()
    {
        return $this->uniqueKeys;
    }

    public function setAllUniqueKeys(array $allColumnsNames)
    {
        $this->uniqueKeys = [];

        foreach($allColumnsNames as $columnsNames) {
            $this->uniqueKeys[] = (array)$columnsNames;
        }

        return $this;
    }

    public function getReferences()
    {
        return $this->references;
    }

    public function setReferences(array $references)
    {
        $this->references = [];

        foreach($references as $reference) {
            $this->addReference($reference);
        }

        return $this;
    }

    public function addReference(TableConstraint $constraint)
    {
        $this->references[] = $constraint;
    }

    public function getRelationships()
    {
        return $this->relationships;
    }

    public function setRelationships(array $references)
    {
        $this->relationships = [];

        foreach($references as $reference) {
            $this->addRelationship($reference);
        }

        return $this;
    }

    public function addRelationship(TableConstraint $constraint)
    {
        $this->relationships[] = $constraint;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function addDependence($name, $tableName, array $filter = [])
    {
        $this->dependencies[$name] = [
            'tableName' => $tableName,
            'filter' => $filter,
        ];

        return $this;
    }

    public function getRelationshipsAliases()
    {
        return $this->relationshipsAliases;
    }

    public function setRelationshipsAliases(array $columnsTypes)
    {
        foreach($columnsTypes as $key => $value) {
            $this->setCustomColumnType($key, $value);
        }

        return $this;
    }

    public function setRelationshipAlias($key, $value)
    {
        $this->relationshipsAliases[(string)$key] = (string)$value;

        return $this;
    }

    public function getRelationshipAlias($key)
    {
        return array_key_exists($key, $this->relationshipsAliases) ? $this->relationshipsAliases[$key] : null;
    }

    public function getReferencesAliases()
    {
        return $this->referencesAliases;
    }

    public function setReferencesAliases(array $columnsTypes)
    {
        foreach($columnsTypes as $key => $value) {
            $this->setCustomColumnType($key, $value);
        }

        return $this;
    }

    public function setReferenceAlias($key, $value)
    {
        $this->referencesAliases[(string)$key] = (string)$value;

        return $this;
    }

    public function getReferenceAlias($key)
    {
        return array_key_exists($key, $this->referencesAliases) ? $this->referencesAliases[$key] : null;
    }

    public function getNameColumn()
    {
        return $this->nameColumn;
    }

    public function setNameColumn($name)
    {
        $this->nameColumn = (string)$name;

        return $this;
    }

    public function getStorage()
    {
        if (!$this->storage) {
            throw new \Exception('Table storage is not defined.');
        }

        return $this->storage;
    }

    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }
}
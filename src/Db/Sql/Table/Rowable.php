<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;
use Greg\Db\Sql\TableRelationship;
use Greg\Engine\InternalTrait;
use Greg\Tool\Arr;
use Greg\Tool\Debug;
use Greg\Tool\Obj;

class Rowable implements RowInterface, \ArrayAccess, \IteratorAggregate, \Serializable, \Countable
{
    use InternalTrait;

    protected $rows = [];

    protected $defaults = [];

    protected $tableRelationships = [];

    protected $table = null;

    protected $total = 0;

    protected $page = 0;

    protected $limit = 0;

    public function __construct(array $rows = [], Table $table = null)
    {
        if ($table) {
            $this->table($table);
        }

        $this->exchange($rows);

        return $this;
    }

    public function exchange(array $rows, array $defaults = [])
    {
        return $this->exchangeRef($rows, $defaults);
    }

    public function exchangeRef(array &$rows, array &$defaults = [])
    {
        $this->rows = &$rows;

        if ($defaults) {
            $this->defaults = &$defaults;
        } else {
            $this->defaults = $rows;
        }

        return $this;
    }

    public function append(array $row, array $default = [])
    {
        return $this->appendRef($row, $default);
    }

    public function appendRef(array &$row, array &$default = [])
    {
        $this->rows[] = &$row;

        if ($default) {
            $this->defaults[] = &$default;
        } else {
            $this->defaults[] = $row;
        }

        return $this;
    }

    public function appendRow(array $row, array $default = [], $isNew = false)
    {
        return $this->appendRowRef($row, $default, $isNew);
    }

    public function appendRowRef(array &$row, array &$default = [], $isNew = false)
    {
        $rowFull = [
            'row' => &$row,
            'isNew' => $isNew,
            'dependencies' => [],
            'references' => [],
            'relationships' => [],
        ];

        $defaultFull = [
            'isNew' => $isNew,
            'dependencies' => [],
            'references' => [],
            'relationships' => [],
        ];

        if ($default) {
            $defaultFull['row'] = &$default;
        } else {
            $defaultFull['row'] = $rowFull;
        }

        $this->rows[] = $rowFull;

        $this->defaults[] = $defaultFull;

        return $this;
    }

    public function tableRelationship($name)
    {
        if (!$relationship = $this->tableRelationships($name)) {
            $relationshipTable = $this->getTable()->getRelationshipTable($name);

            $relationship = $this->newTableRelationship($relationshipTable, $this);

            $this->tableRelationships($name, $relationship);
        }

        return $relationship;
    }

    protected function &getRelationshipAssoc($name)
    {
        $relationships = &$this->firstAssoc('relationships');

        Arr::bringRef($relationships);

        return Arr::getArrayRef($relationships, $name);
    }

    protected function &getRelationshipAssocDefault($name)
    {
        $relationships = &$this->firstAssocDefault('relationships');

        Arr::bringRef($relationships);

        return Arr::getArrayRef($relationships, $name);
    }

    public function getRelationship($name)
    {
        $relationship = Arr::get(array_flip($this->getTable()->relationshipsAliases()), $name, $name);

        $tableName = current($parts = explode('.', $relationship));

        $table = $this->getTable()->getRelationshipTable($tableName);

        $rows = &$this->getRelationshipAssoc($name);

        $rowsDefault = &$this->getRelationshipAssocDefault($name);

        return $table->createRowable([], false)->exchangeRef($rows, $rowsDefault);
    }

    protected function &getReferenceAssoc($name)
    {
        $references = &$this->firstAssoc('references');

        Arr::bringRef($references);

        return Arr::getArrayRef($references, $name);
    }

    protected function &getReferenceAssocDefault($name)
    {
        $references = &$this->firstAssocDefault('references');

        Arr::bringRef($references);

        return Arr::getArrayRef($references, $name);
    }

    /**
     * @param $name
     * @return Rowable|null
     * @throws \Exception
     */
    public function getReference($name)
    {
        $row = &$this->getReferenceAssoc($name);

        if (!$row) {
            return null;
        }

        $reference = Arr::get(array_flip($this->getTable()->referencesAliases()), $name, $name);

        $table = $this->getTable()->getReferenceTableByColumn($reference);

        $rowDefault = &$this->getReferenceAssocDefault($name);

        return $table->createRowable([], false)->appendRef($row, $rowDefault);
    }

    /**
     * @return Table
     * @throws \Exception
     */
    public function getTable()
    {
        if (!($table = $this->table())) {
            throw new \Exception('Please define a table for this rowable.');
        }

        return $table;
    }

    public function getTableName()
    {
        return $this->getTable()->getName();
    }

    public function toArray()
    {
        return $this->rows;
    }

    public function column($column)
    {
        $items = [];

        foreach($this->rows as $row) {
            $items[] = $row['row'][$column];
        }

        return $items;
    }

    public function getAll($column)
    {
        $items = [];

        foreach($this->getIterator() as $row) {
            $items[] = $row->get($column);
        }

        return $items;
    }

    public function find(callable $callable = null)
    {
        foreach($this->getIterator() as $key => $value) {
            if (call_user_func_array($callable, [$value, $key])) return $value;
        }

        return null;
    }

    /* START rows methods */

    public function set($key, $value = null)
    {
        foreach($this->rows as &$row) {
            if (is_array($key)) {
                $row['row'] = array_replace($row['row'], $key);
            } else {
                $row['row'][$key] = $value;
            }
        }

        return $this;
    }

    public function save()
    {
        foreach($this->rows as $key => &$row) {
            $default = &$this->defaults[$key];

            $data = $this->getTable()->parseData($row['row']);

            if (Arr::get($row, 'isNew')) {
                $this->getTable()->insert($data)->exec();

                $default['isNew'] = $row['isNew'] = false;

                if ($column = $this->getTable()->autoIncrement()) {
                    $data[$column] = $this->getTable()->lastInsertId();
                }
            } else {
                if ($data = array_diff_assoc($data, $default['row'])) {
                    $this->getTable()
                        ->update($data)
                        ->whereCols($this->getFirstUniqueFromRow($default['row']))
                        ->exec();
                }
            }

            $default['row'] = $row['row'] = array_replace($row['row'], $data);
        }
        unset($row);

        return $this;
    }

    public function update(array $data)
    {
        $this->set($data);

        $this->save();

        return $this;
    }

    public function delete()
    {
        $keys = [];

        foreach($this->rows as $key => &$row) {
            $default = &$this->defaults[$key];

            $keys[] = $this->getFirstUniqueFromRow($default['row']);

            $default['isNew'] = $row['isNew'] = true;
        }
        unset($row);

        $table = $this->getTable();

        $query = $table->delete()->whereCol($table->getFirstUnique(), $keys);

        $query->exec();

        return $this;
    }

    /* END rows methods */

    /* START First row methods */

    /**
     * @return $this
     */
    public function first()
    {
        $row = &$this->firstAssoc();

        if (!$row) {
            return null;
        }

        return $this->newRowable([], $this->table())->appendRef($row, $this->firstAssocDefault());
    }

    protected function &firstAssoc($key = null)
    {
        $row = &Arr::first($this->rows);

        if ($row and $key) {
            return Arr::getRef($row, $key);
        }

        return $row;
    }

    protected function &firstAssocDefault($key = null)
    {
        $row = &Arr::first($this->defaults);

        if ($row and $key) {
            return Arr::getRef($row, $key);
        }

        return $row;
    }

    protected function &firstAssocRow($key = null, $value = null)
    {
        $row = &$this->firstAssoc('row');

        if (func_num_args() === 1 and !is_array($key) and !array_key_exists($key, $row)) {
            $methodName = 'let' . ucfirst($key);

            if (method_exists($this, $methodName)) {
                $value = $this->callCallableWith([$this, $methodName]);

                return $value;
            }
        }

        return Obj::fetchArrayReplaceVar($this, $row, ...func_get_args());
    }

    protected function &firstAssocDefaultRow($key = null, $value = null)
    {
        return Obj::fetchArrayReplaceVar($this, $this->firstAssocDefault('row'), ...func_get_args());
    }

    public function autoIncrement()
    {
        return ($key = $this->getTable()->autoIncrement()) ? $this->firstAssocDefaultRow($key) : null;
    }

    public function primary()
    {
        $keys = [];

        foreach($this->getTable()->primary() as $key) {
            $keys[$key] = $this->firstAssocDefaultRow($key);
        }

        return $keys;
    }

    public function unique()
    {
        $keys = [];

        foreach($this->getTable()->unique() as $name => $info) {
            foreach($info['Keys'] as $key) {
                $keys[$name][$key['ColumnName']] = $this->firstAssocDefaultRow($key['ColumnName']);
            }
        }

        return $keys;
    }

    public function getFirstUnique()
    {
        return $this->getFirstUniqueFromRow($this->firstAssocDefaultRow());
    }

    public function get($column)
    {
        if (is_array($column)) {
            $values = [];

            foreach($column as $key => $name) {
                $filter = null;

                if (is_array($name)) {
                    $args = $name;

                    $name = array_shift($args);

                    $filter = array_shift($args);
                }

                $value = $this->firstAssocRow((string)$name);

                if (is_callable($filter)) {
                    $value = $this->callCallable($filter, $value);
                }

                $values[is_int($key) ? $name : $key] = $value;
            }

            return $values;
        }

        return $this->firstAssocRow($column);
    }

    public function offsetExists($offset)
    {
        return Arr::hasRef($this->firstAssocRow(), $offset);
    }


    public function offsetGet($offset)
    {
        return $this->firstAssocRow($offset);
    }


    public function offsetSet($offset, $value)
    {
        Arr::set($this->firstAssocRow(), $offset, $value);

        return $this;
    }

    public function offsetUnset($offset)
    {
        Arr::del($this->firstAssocRow(), $offset);

        return $this;
    }

    /* END First row methods */

    protected function getFirstUniqueFromRow(array $row)
    {
        $keys = [];

        foreach($this->getTable()->getFirstUnique() as $key) {
            $keys[$key] = $row[$key];
        }

        return $keys;
    }

    protected function newTableRelationship(Table $table, RowInterface $rowable)
    {
        return new TableRelationship($table, $rowable);
    }

    /**
     * @param Table $value
     * @return $this|Table
     */
    public function table(Table $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function tableRelationships($key = null, $value = null, $type = Obj::PROP_APPEND)
    {
        return Obj::fetchArrayReplaceVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    /**
     * @param array $dataSet
     * @param Table|null $table
     * @return static
     */
    protected function newRowable(array $dataSet = [], Table $table = null)
    {
        $class = get_called_class();

        return new $class($dataSet, $table);
    }

    /**
     * @return static[]
     */
    public function getIterator()
    {
        foreach($this->rows as $key => &$row) {
            yield $this->newRowable([], $this->table())->appendRef($row, $this->defaults[$key]);
        }
    }

    public function serialize()
    {
        return serialize([
            'rows' => $this->rows,
            'defaults' => $this->defaults,
        ]);
    }

    public function unserialize($storage)
    {
        $data = unserialize($storage);

        $this->rows = $data['rows'];

        $this->defaults = $data['defaults'];

        return $this;
    }

    public function count()
    {
        return sizeof($this->rows);
    }

    public function maxPage()
    {
        $maxPage = 1;

        if (($total = $this->total()) > 0) {
            $maxPage = ceil($total / $this->limit());
        }

        return $maxPage;
    }

    public function prevPage()
    {
        $page = $this->page() - 1;

        return $page > 1 ? $page : 1;
    }

    public function nextPage()
    {
        $page = $this->page() + 1;

        $maxPage = $this->maxPage();

        return $page > $maxPage ? $maxPage : $page;
    }

    public function hasMorePages()
    {
        return $this->page() < $this->maxPage();
    }

    public function total($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function page($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function limit($value = null)
    {
        return Obj::fetchIntVar($this, $this->{__FUNCTION__}, true, ...func_get_args());
    }

    public function __debugInfo()
    {
        return Debug::fixInfo($this, get_object_vars($this), false);
    }
}
<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;
use Greg\Db\Sql\StorageInterface;

trait JoinsTrait
{
    protected $joins = [];

    public function left($table, $on = null, $param = null, $_ = null)
    {
        return $this->join('LEFT', ...func_get_args());
    }

    public function right($table, $on = null, $param = null, $_ = null)
    {
        return $this->join('RIGHT', ...func_get_args());
    }

    public function inner($table, $on = null, $param = null, $_ = null)
    {
        return $this->join('INNER', ...func_get_args());
    }

    public function leftTo($source, $table, $on = null, $param = null, $_ = null)
    {
        return $this->join('LEFT', ...func_get_args());
    }

    public function rightTo($source, $table, $on = null, $param = null, $_ = null)
    {
        return $this->join('RIGHT', ...func_get_args());
    }

    public function innerTo($source, $table, $on = null, $param = null, $_ = null)
    {
        return $this->join('INNER', ...func_get_args());
    }

    protected function join($type, $source, $table, $on = null, $param = null, $_ = null)
    {
        if (is_callable($on)) {
            $query = $this->newOn();

            $this->callCallable($on, $query);

            $on = $query->toString();

            $params = $query->bindParams();
        } else {
            $params = is_array($param) ? $param : array_slice(func_get_args(), 4);
        }

        $this->joins[] = [
            'type' => $type,
            'source' => $source,
            'table' => $table,
            'on' => $on,
            'params' => $params,
        ];

        return $this;
    }

    protected function newOn()
    {
        return new On($this->storage());
    }

    public function joinsToString($source)
    {
        if (!$this->joins) {
            return '';
        }

        $joins = [];

        $sourceParts = array_filter($this->fetchAlias($source));


        foreach($this->joins as $join) {
            $joinSourceParts = ($this->fetchAlias($join['source']));

            if ((!$sourceParts and !$joinSourceParts) or array_intersect($sourceParts, $joinSourceParts)) {
                list($alias, $table) = $this->fetchAlias($join['table']);

                unset($alias);

                if ($table instanceof Query) {
                    $this->bindParams($table->bindParams());
                }

                $expr = ($join['type'] ? $join['type'] . ' ' : '') . 'JOIN ' . $this->quoteAliasExpr($join['table']);

                if ($join['on']) {
                    $expr .= ' ON ' . $this->quoteExpr($join['on']);

                    $this->bindParams($join['params']);
                }

                $joins[] = $expr;
            }
        }

        return implode('', $joins);
    }

    /**
     * @param StorageInterface $value
     * @return $this|null|StorageInterface
     */
    abstract public function storage(StorageInterface $value = null);

    abstract protected function quoteAliasExpr($expr);

    abstract protected function fetchAlias($name);

    abstract protected function quoteExpr($expr);

    abstract protected function bindParams(array $params = []);

    abstract protected function callCallable(callable $callable, ...$args);
}
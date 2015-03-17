<?php

namespace Greg\Db\Sql;

use Greg\Db\Sql\Query\Expr;
use Greg\Db\Sql\Storage\Adapter\StmtInterface;
use Greg\Support\Arr;
use Greg\Support\Obj;
use Greg\Support\Str;

trait QueryTrait
{
    protected $quoteNameWith = '`';

    protected $storage = null;

    protected $params = [];

    public function __construct(StorageInterface $storage)
    {
        $this->storage($storage);
    }

    protected function fetchAlias($name)
    {
        if (is_array($name)) {
            return [key($name), current($name)];
        }

        if (is_scalar($name)) {
            if (preg_match('#^(.+?)(?:\s+as\s+([a-z0-9_]+))?$#i', $name, $matches)) {
                return [isset($matches[2]) ? $matches[2] : null, $matches[1]];
            }
        }

        if (($name instanceof Table)) {
            return [$name->alias(), $name->name()];
        }

        return [null, $name];
    }

    protected function isCleanColumn($expr, $includeAlias = true)
    {
        if (($expr instanceof Expr)) {
            return false;
        }

        if ($expr == '*') {
            return true;
        }

        $regex = '([a-z0-9_]+)';

        if ($includeAlias) {
            $regex .= '(?:\s+as\s+([a-z0-9_]+))?';
        }

        return preg_match('#^' . $regex . '$#i', $expr);
    }

    protected function quoteAliasExpr($expr)
    {
        list($alias, $expr) = $this->fetchAlias($expr);

        $expr = $this->quoteNamedExpr($expr);

        if ($alias) {
            $expr .= ' AS ' . $this->quoteName($alias);
        }

        return $expr;
    }

    protected function quoteNamedExpr($expr)
    {
        if (($expr instanceof Expr)) {
            return $expr;
        }

        if (preg_match('#^[a-z0-9_\.\*]+$#i', $expr)) {
            $expr = explode('.', $expr);

            $expr = array_map(function($part) {
                return $part !== '*' ? $this->quoteName($part) : $part;
            }, $expr);

            $expr = implode('.', $expr);
        }

        return $expr;
    }

    protected function quoteExpr($expr)
    {
        if (($expr instanceof Expr)) {
            return $expr;
        }

        $expr = $this->quoteNamedExpr($expr);

        $expr = preg_replace_callback('#".*\![a-z0-9_\.\*]+.*"|\!([a-z0-9_\.\*]+)#i', function($matches) {
            return isset($matches[1]) ? $this->quoteNamedExpr($matches[1]) : $matches[0];
        }, $expr);

        return $expr;
    }

    protected function quoteName($name)
    {
        return Str::quote($name, $this->quoteNameWith());
    }

    public function quoteNameWith($value = null, $type = Obj::PROP_REPLACE)
    {
        return Obj::fetchStrVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }

    protected function bindParams(array $params = [])
    {
        if (func_num_args()) {
            $this->params = array_merge($this->params, $params);

            return $this;
        }

        return $this->params;
    }

    protected function bindParamsToStmt(StmtInterface $stmt)
    {
        $k = 1;

        foreach($this->bindParams() as $key => $param) {
            Arr::bringRef($param);

            array_unshift($param, is_int($key) ? $k++ : $key);

            $stmt->bindValue(...$param);
        }

        return $this;
    }

    public function clearBindParams()
    {
        $this->params = [];

        return $this;
    }

    /**
     * @param StorageInterface $value
     * @return StorageInterface|$this|null
     */
    public function storage(StorageInterface $value = null)
    {
        return Obj::fetchVar($this, $this->{__FUNCTION__}, ...func_get_args());
    }
}
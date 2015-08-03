<?php

namespace Greg\Support\Db\Sql\Query;

use Greg\Support\Db\Sql\Query;

trait FromTrait
{
    protected $from = [];

    public function from($table = null)
    {
        if (func_num_args()) {
            $this->from[] = $table;

            return $this;
        }

        return $this->from;
    }

    public function fromToString()
    {
        $from = [];

        foreach($this->from as $name) {
            $from[] = $this->quoteAliasExpr($name);

            list($alias, $expr) = $this->fetchAlias($name);

            unset($alias);

            if ($expr instanceof Query) {
                $this->bindParams($expr->bindParams());
            }
        }

        return $from ? 'FROM ' . implode(', ', $from) : '';
    }

    abstract protected function quoteAliasExpr($expr);

    abstract protected function fetchAlias($name);

    abstract protected function bindParams(array $params = []);
}
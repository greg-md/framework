<?php

namespace Greg\Db\Sql\Query;

use Greg\Db\Sql\Query;

trait FromTrait
{
    use JoinsTrait;

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
            $expr = $this->quoteAliasExpr($name);

            list($alias, $table) = $this->fetchAlias($name);

            unset($alias);

            if ($table instanceof Query) {
                $this->bindParams($table->bindParams());
            }

            if ($joins = $this->joinsToString($name)) {
                $expr .= ' ' . $joins;
            }

            $from[] = $expr;
        }

        $query = $from ? 'FROM ' . implode(', ', $from) : '';

        if ($joins = $this->joinsToString(null)) {
            $query .= ' ' . $joins;
        }

        return $query;
    }
}
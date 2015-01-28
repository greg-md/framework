<?php

namespace Greg\Db\Query\Traits;

trait From
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
        }

        return $from ? 'FROM ' . implode(', ', $from) : '';
    }

    abstract protected function quoteAliasExpr($expr);
}
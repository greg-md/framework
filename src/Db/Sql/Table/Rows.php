<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;

class Rows extends RowAbstract
{
    public function toArray()
    {
        $items = parent::toArray();

        /* @var $item Row */
        foreach($items as &$item) {
            if ($item instanceof Row) {
                $item = $item->toArray();
            }
        }
        unset($item);

        return $items;
    }

    public function toArrayObject()
    {
        $items = parent::toArrayObject();

        /* @var $item Row */
        foreach($items as &$item) {
            if ($item instanceof Row) {
                $item = $item->toArrayObject();
            }
        }
        unset($item);

        return $items;
    }
}
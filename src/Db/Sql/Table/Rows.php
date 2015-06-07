<?php

namespace Greg\Db\Sql\Table;

use Greg\Db\Sql\Table;

class Rows extends RowAbstract
{
    public function toArray($recursive = true)
    {
        $items = parent::toArray();

        if ($recursive) {
            foreach($items as &$item) {
                if ($item instanceof Row) {
                    $item = $item->toArray();
                }
            }
            unset($item);
        }

        return $items;
    }

    public function toArrayObject($recursive = true)
    {
        $items = parent::toArrayObject();

        if ($recursive) {
            foreach($items as &$item) {
                if ($item instanceof Row) {
                    $item = $item->toArrayObject();
                }
            }
            unset($item);
        }

        return $items;
    }
}
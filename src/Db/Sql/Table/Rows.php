<?php

namespace Greg\Db\Sql\Table;

class Rows extends RowAbstract
{
    public function delete()
    {
        foreach($this->items() as $row) {
            $row->delete();
        }

        return $this;
    }

    public function items()
    {
        return $this->toArray(false);
    }

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
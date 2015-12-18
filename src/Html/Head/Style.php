<?php

namespace Greg\Html\Head;

use Greg\Html\Element;
use Greg\Html\Script;
use Greg\Tool\Arr;

class Style extends Script
{
    public function fetchItem($item)
    {
        $attr = Arr::bring($item['attr']);

        $attr['rel'] = 'stylesheet';

        $element = new Element($item['inner'] ? 'style' : 'link', $attr, $item['condition']);

        $element->inner($item['inner']);

        return $element;
    }
}
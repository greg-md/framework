<?php

namespace Greg\Support\Html\Head;

use Greg\Support\Html\Element;
use Greg\Support\Html\Script;
use Greg\Support\Tool\Arr;

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
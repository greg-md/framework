<?php

namespace Greg\Html;

use Greg\Tool\Arr;

class HeadStyle extends HtmlScript
{
    public function fetchItem($item)
    {
        $attr = Arr::bring($item['attr']);

        $attr['rel'] = 'stylesheet';

        $element = new HtmlElement($item['inner'] ? 'style' : 'link', $attr, $item['condition']);

        $element->inner($item['inner']);

        return $element;
    }
}
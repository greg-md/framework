<?php

namespace Greg\Html;

class HeadStyle extends HtmlScript
{
    public function fetchItem($item)
    {
        $attr = (array) $item['attr'];

        $attr['rel'] = 'stylesheet';

        $element = new HtmlElement($item['inner'] ? 'style' : 'link', $attr, $item['condition']);

        $element->setInner($item['inner']);

        return $element;
    }
}

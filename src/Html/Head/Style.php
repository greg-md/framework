<?php

namespace Greg\Html\Head;

use Greg\Html\Element;
use Greg\Html\Script;

class Style extends Script
{
    public function fetchItem($item)
    {
        $attr = isset($item['attr']) ? $item['attr'] : [];

        $condition = isset($item['condition']) ? $item['condition'] : null;

        $attr['rel'] = 'stylesheet';

        if (isset($item['inner']) and $item['inner']) {
            $element = Element::create($this->appName(), 'style', $attr, $condition);

            $element->inner($item['inner']);
        } else {
            $element = Element::create($this->appName(), 'link', $attr, $condition);
        }

        return $element;
    }
}
<?php

namespace Greg\Framework\Html;

use PHPUnit\Framework\TestCase;

class HtmlElementTest extends TestCase
{
    public function testCanInstantiate()
    {
        $meta = new HtmlElement('a');

        $this->assertInstanceOf(HtmlElement::class, $meta);
    }
}
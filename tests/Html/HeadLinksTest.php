<?php

namespace Greg\Framework\Html;

use PHPUnit\Framework\TestCase;

class HeadLinksTest extends TestCase
{
    public function testCanInstantiate()
    {
        $links = new HeadLinks();

        $this->assertInstanceOf(HeadLinks::class, $links);
    }

    public function testCanSet()
    {
        $links = new HeadLinks();

        $links->set('index', 'http://google.com/');

        $this->assertEquals('<link rel="index" href="http://google.com/" />', $links->toString());
    }

    public function testCanSetIcon()
    {
        $links = new HeadLinks();

        $links->icon('favicon.png', 'image/png');

        $this->assertEquals('<link rel="icon" href="favicon.png" type="image/png" />', $links->toString());
    }

    public function testCanSetStyle()
    {
        $links = new HeadLinks();

        $links->style('styles.css');

        $this->assertEquals('<link rel="stylesheet" href="styles.css" />', $links->toString());
    }

    public function testCanSetCustomLinks()
    {
        $links = new HeadLinks();

        $links->alternate('http://google.com/');

        $this->assertEquals('<link rel="alternate" href="http://google.com/" />', $links->toString());
    }

    public function testCanActAsString()
    {
        $links = new HeadLinks();

        $links->style('styles.css');

        $this->assertEquals('<link rel="stylesheet" href="styles.css" />', (string) $links);
    }
}

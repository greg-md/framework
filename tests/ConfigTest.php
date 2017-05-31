<?php

namespace Greg\Framework;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testCanCreateNewInstance()
    {
        $config = new Config(['foo' => 'bar']);

        $this->assertEquals('bar', $config['foo']);
    }
}

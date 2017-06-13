<?php

namespace Greg\Framework\Html;

use PHPUnit\Framework\TestCase;

class HeadMetaTest extends TestCase
{
    public function testCanInstantiate()
    {
        $meta = new HeadMeta();

        $this->assertInstanceOf(HeadMeta::class, $meta);
    }

    public function testCanGetMeta()
    {
        $meta = new HeadMeta();

        $meta->name('description', 'I am a description');

        $this->assertEquals(['name' => 'description', 'content' => 'I am a description'], $meta->get('description'));
    }

    public function testCanSetName()
    {
        $meta = new HeadMeta();

        $meta->name('description', 'I am a description');

        $this->assertEquals('<meta name="description" content="I am a description" />', $meta->toString());
    }

    public function testCanSetProperty()
    {
        $meta = new HeadMeta();

        $meta->property('name', 'value');

        $this->assertEquals('<meta property="name" content="value" />', $meta->toString());
    }

    public function testCanSetHttpEquiv()
    {
        $meta = new HeadMeta();

        $meta->httpEquiv('refresh', '10; url=http://google.com/');

        $this->assertEquals('<meta http-equiv="refresh" content="10; url=http://google.com/" />', $meta->toString());
    }

    public function testCanSetCharset()
    {
        $meta = new HeadMeta();

        $meta->charset('utf-8');

        $this->assertEquals('<meta charset="utf-8" />', $meta->toString());
    }

    public function testCanSetRefresh()
    {
        $meta = new HeadMeta();

        $meta->refresh(10, 'http://google.com/');

        $this->assertEquals('<meta http-equiv="refresh" content="10; url=http://google.com/" />', $meta->toString());
    }

    public function testCanSetAuthor()
    {
        $meta = new HeadMeta();

        $meta->author('John Doe');

        $this->assertEquals('<meta name="author" content="John Doe" />', $meta->toString());
    }

    public function testCanSetDescription()
    {
        $meta = new HeadMeta();

        $meta->description('John Doe');

        $this->assertEquals('<meta name="description" content="John Doe" />', $meta->toString());
    }

    public function testCanSetGenerator()
    {
        $meta = new HeadMeta();

        $meta->generator('John Doe');

        $this->assertEquals('<meta name="generator" content="John Doe" />', $meta->toString());
    }

    public function testCanSetKeywords()
    {
        $meta = new HeadMeta();

        $meta->keywords('John Doe');

        $this->assertEquals('<meta name="keywords" content="John Doe" />', $meta->toString());
    }

    public function testCanSetViewPort()
    {
        $meta = new HeadMeta();

        $meta->viewport('width=device-width, initial-scale=1.0');

        $this->assertEquals('<meta name="viewport" content="width=device-width, initial-scale=1.0" />', $meta->toString());
    }

    public function testCanActAsString()
    {
        $meta = new HeadMeta();

        $meta->keywords('John Doe');

        $this->assertEquals('<meta name="keywords" content="John Doe" />', (string) $meta);
    }
}

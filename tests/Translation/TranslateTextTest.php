<?php

namespace Greg\Framework\Translation;

use PHPUnit\Framework\TestCase;

class TranslateTextTest extends TestCase
{
    public function testCanInstantiate()
    {
        $text = new TranslateText('Hello %s!');

        $this->assertInstanceOf(TranslateText::class, $text);
    }

    public function testCanApply()
    {
        $text = new TranslateText('Hello %s!');

        $this->assertEquals('Hello John!', $text->apply('John'));
    }

    public function testCanApplyArguments()
    {
        $this->assertEquals(
            'Hello John! You have 100 credits.',
            TranslateText::applyArguments('Hello {name}! You have %d credits.', ['name' => 'John', 100])
        );
    }

    public function testCanInvoke()
    {
        $text = new TranslateText('Hello %s!');

        $this->assertEquals('Hello John!', $text('John'));
    }

    public function testCanActAsString()
    {
        $text = new TranslateText('Hello %s!');

        $this->assertEquals('Hello %s!', (string) $text);
    }
}

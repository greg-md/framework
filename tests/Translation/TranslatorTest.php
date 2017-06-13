<?php

namespace Greg\Framework\Translation;

use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    public function testCanInstantiate()
    {
        $translator = new Translator();

        $this->assertInstanceOf(Translator::class, $translator);
    }

    public function testCanDetermineIfHasLanguages()
    {
        $translator = new Translator();

        $this->assertFalse($translator->hasLanguages());

        $translator->addLanguage('en_EN');

        $this->assertTrue($translator->hasLanguages());
    }

    public function testCanGetLanguages()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $this->assertArrayHasKey('en_EN', $translator->getLanguages());
    }

    public function testCanClearLanguages()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->addLanguage('ro_RO', ['name' => 'Romanian']);

        $translator->addLanguage('ru_RU', ['name' => 'Russian'], function() {
            return [];
        });

        $translator->setDefaultLanguage('en_EN');

        $translator->setCurrentLanguage('ru_RU');

        $this->assertTrue($translator->hasLanguages());

        $translator->clearLanguages();

        $this->assertFalse($translator->hasLanguages());

        $this->assertEquals(setlocale(LC_CTYPE, 0), $translator->getDefaultLanguage());

        $this->assertEquals(setlocale(LC_CTYPE, 0), $translator->getCurrentLanguage());

        $this->assertFalse($translator->hasTranslatesLoaders());

        $this->assertFalse($translator->hasAnyTranslates());
    }

    public function testCanGetLanguage()
    {
        $translator = new Translator();

        $this->assertNull($translator->getLanguage('en_EN'));

        $translator->addLanguage('en_EN', ['name' => 'English']);

        $this->assertEquals(['name' => 'English'], $translator->getLanguage('en_EN'));
    }

    public function testCanAddLanguage()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->addLanguage('ro_RO', ['name' => 'Romanian']);

        $translator->addLanguage('ru_RU', ['name' => 'Russian'], function() {
            return ['translate_key' => 'translate_text'];
        });

        $this->assertEquals([
            'en_EN' => [],
            'ro_RO' => ['name' => 'Romanian'],
            'ru_RU' => ['name' => 'Russian'],
        ], $translator->getLanguages());

        $this->assertFalse($translator->hasTranslateLoader('en_EN'));

        $this->assertFalse($translator->hasTranslateLoader('ro_RO'));

        $this->assertTrue($translator->hasTranslateLoader('ru_RU'));
    }

    public function testCanRemoveLanguage()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->setDefaultLanguage('en_EN');

        $translator->setCurrentLanguage('en_EN');

        $this->assertTrue($translator->hasLanguage('en_EN'));

        $translator->removeLanguage('en_EN');

        $this->assertFalse($translator->hasLanguage('en_EN'));

        $this->assertEquals(setlocale(LC_CTYPE, 0), $translator->getDefaultLanguage());

        $this->assertEquals(setlocale(LC_CTYPE, 0), $translator->getCurrentLanguage());
    }

    public function testCanGetLocales()
    {
        $translator = new Translator();

        $this->assertEmpty($translator->getLocales());

        $translator->addLanguage('en_EN');

        $this->assertContains('en_EN', $translator->getLocales());
    }

    public function testCanSetDefaultLanguage()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->setDefaultLanguage('en_EN');

        $this->assertEquals('en_EN', $translator->getDefaultLanguage());
    }

    public function testCanThrowExceptionIfDefaultLanguageIsUndefinedOnSet()
    {
        $translator = new Translator();

        $this->expectException(\Exception::class);

        $translator->setDefaultLanguage('en_EN');
    }

    public function testCanSetCurrentLanguage()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->setCurrentLanguage('en_EN');

        $this->assertEquals('en_EN', $translator->getCurrentLanguage());
    }

    public function testCanThrowExceptionIfCurrentLanguageIsUndefinedOnSet()
    {
        $translator = new Translator();

        $this->expectException(\Exception::class);

        $translator->setCurrentLanguage('en_EN');
    }

    public function testCanGetTranslateLoaders()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->addLanguage('ro_RO', [], function () {});

        $this->assertArrayNotHasKey('en_EN', $translator->getTranslatesLoaders());

        $this->assertArrayHasKey('ro_RO', $translator->getTranslatesLoaders());
    }

    public function testCanClearTranslateLoaders()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN', [], function () {});

        $translator->addLanguage('ro_RO', [], function () {});

        $this->assertTrue($translator->hasTranslatesLoaders());

        $translator->clearTranslatesLoaders();

        $this->assertFalse($translator->hasTranslatesLoaders());
    }

    public function testCanGetTranslateLoader()
    {
        $translator = new Translator();

        $translator->addLanguage('ro_RO', [], $callable = function () {});

        $this->assertEquals($callable, $translator->getTranslateLoader('ro_RO'));
    }

    public function testCanAddTranslateLoader()
    {
        $translator = new Translator();

        $translator->addLanguage('ro_RO');

        $translator->addTranslateLoader('ro_RO', $callable = function () {});

        $this->assertEquals($callable, $translator->getTranslateLoader('ro_RO'));
    }

    public function testCanRemoveTranslateLoader()
    {
        $translator = new Translator();

        $translator->addLanguage('ro_RO');

        $translator->addTranslateLoader('ro_RO', $callable = function () {});

        $this->assertTrue($translator->hasTranslateLoader('ro_RO'));

        $translator->removeTranslateLoader('ro_RO');

        $this->assertFalse($translator->hasTranslateLoader('ro_RO'));
    }

    public function testCanDetermineIfHasAnyTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $this->assertFalse($translator->hasAnyTranslates());

        $translator->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertTrue($translator->hasAnyTranslates());
    }

    public function testCanGetAllTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->addLanguage('ro_RO')->setTranslates('ro_RO', ['translate_key' => 'translate_text']);

        $this->assertArrayHasKey('en_EN', $translator->getAllTranslates());

        $this->assertArrayHasKey('ro_RO', $translator->getAllTranslates());
    }

    public function testCanClearTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertTrue($translator->hasAnyTranslates());

        $translator->clearTranslates();

        $this->assertFalse($translator->hasAnyTranslates());
    }

    public function testCanDetermineIfHasTranslates()
    {
        $translator = new Translator();

        $this->assertFalse($translator->hasTranslates('en_EN'));

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertTrue($translator->hasTranslates('en_EN'));
    }

    public function testCanGetTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $translator->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertEquals(['translate_key' => 'translate_text'], $translator->getTranslates('en_EN'));
    }

    public function testCanAddTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->addTranslates('en_EN', ['translate_new_key' => 'translate_new_text']);

        $this->assertArrayHasKey('translate_key', $translator->getTranslates('en_EN'));

        $this->assertArrayHasKey('translate_new_key', $translator->getTranslates('en_EN'));
    }

    public function testCanRemoveTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertTrue($translator->hasTranslates('en_EN'));

        $translator->removeTranslates('en_EN');

        $this->assertFalse($translator->hasTranslates('en_EN'));
    }

    public function testCanDetermineIfHasTranslate()
    {
        $translator = new Translator();

        $this->assertFalse($translator->hasTranslate('en_EN', 'translate_key'));

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertTrue($translator->hasTranslate('en_EN', 'translate_key'));
    }

    public function testCanGetTranslate()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertEquals('translate_text', $translator->getTranslate('en_EN', 'translate_key'));
    }

    public function testCanAddTranslate()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->addTranslate('en_EN', 'translate_new_key', 'translate_new_text');

        $this->assertArrayHasKey('translate_key', $translator->getTranslates('en_EN'));

        $this->assertArrayHasKey('translate_new_key', $translator->getTranslates('en_EN'));
    }

    public function testCanRemoveTranslate()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertTrue($translator->hasTranslate('en_EN', 'translate_key'));

        $translator->removeTranslate('en_EN', 'translate_key');

        $this->assertFalse($translator->hasTranslate('en_EN', 'translate_key'));
    }

    public function testCanAddLocaleLanguage()
    {
        $translator = new Translator();

        $translator->addLocaleLanguage();

        $this->assertContains(setlocale(LC_CTYPE, 0), $translator->getLocales());
    }

    public function testCanDetermineIfAnLanguageIsDefaultOne()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN');

        $this->assertFalse($translator->isDefault('en_EN'));

        $translator->setDefaultLanguage('en_EN');

        $this->assertTrue($translator->isDefault('en_EN'));
    }

    public function testCanGetCurrentTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->setCurrentLanguage('en_EN');

        $this->assertArrayHasKey('translate_key', $translator->currentTranslates());
    }

    public function testCanTranslate()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertEquals('translate_key', $translator->translate('translate_key'));

        $translator->setCurrentLanguage('en_EN');

        $this->assertEquals('translate_text', $translator->translate('translate_key'));
    }

    public function testCanTranslateKey()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $this->assertEquals('translate text', $translator->translateKey('translate_key', 'translate text'));

        $translator->setCurrentLanguage('en_EN');

        $this->assertEquals('translate_text', $translator->translate('translate_key'));
    }

    public function testCanTranslateWithArguments()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['hello' => 'Hello {name}! You have %d credits.']);

        $translator->setCurrentLanguage('en_EN');

        $this->assertEquals('Hello John! You have 100 credits.', $translator->translate('hello', ['name' => 'John', 100]));
    }
    
    public function testCanGetNewTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->setCurrentLanguage('en_EN');

        $this->assertEquals('translate_text', $translator->translate('translate_key'));

        $this->assertEquals('translate_new_key', $translator->translate('translate_new_key'));

        $this->assertEquals([
            'en_EN' => [
                'translate_new_key' => 'translate_new_key',
            ],
        ], $translator->newTranslates());
    }

    public function testCanActAsArrayReturningCurrentTranslates()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->setCurrentLanguage('en_EN');

        $this->assertTrue(isset($translator['translate_key']));

        $this->assertInstanceOf(TranslateText::class, $translator['translate_key']);

        $this->assertEquals('translate_text', $translator['translate_key']);

        $translator['translate_new_key'] = 'translate_new_text';

        $this->assertEquals('translate_new_text', $translator['translate_new_key']);

        unset($translator['translate_new_key']);

        $this->assertFalse(isset($translator['translate_new_key']));
    }

    public function testCanInvoke()
    {
        $translator = new Translator();

        $translator->addLanguage('en_EN')->setTranslates('en_EN', ['translate_key' => 'translate_text']);

        $translator->setCurrentLanguage('en_EN');

        $this->assertEquals('translate_text', $translator('translate_key'));
    }
}
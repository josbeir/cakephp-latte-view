<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Latte\Extension;

use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use LatteView\Latte\Extension\Translator;

class TranslatorTest extends TestCase
{
    protected ?Translator $translator = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = new Translator();
        I18n::setLocale('en_US');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->translator = null;
        I18n::setLocale('en_US');
    }

    public function testSimpleTranslation(): void
    {
        $result = $this->translator->translate('Hello World');
        $this->assertSame('Hello World', $result);
    }

    public function testTranslationWithDomain(): void
    {
        $result = $this->translator->translate('Hello', domain: 'custom');
        $this->assertSame('Hello', $result);
    }

    public function testPluralTranslationMissingSingularThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both `count` and `singular` arguments must be provided for plural translation');

        $this->translator->translate('items', count: 1);
    }

    public function testPluralTranslationMissingCountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both `count` and `singular` arguments must be provided for plural translation');

        $this->translator->translate('items', singular: '1 item');
    }

    public function testPluralTranslationNonNumericCountThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Count argument must be numeric when using singular argument');

        $this->translator->translate(
            'items',
            singular: '1 item',
            count: 'invalid',
        );
    }

    public function testTranslateMethodExists(): void
    {
        $this->assertTrue(method_exists($this->translator, 'translate'));
    }

    public function testTranslatorClass(): void
    {
        $this->assertInstanceOf(Translator::class, $this->translator);
    }

    public function testDomainHandling(): void
    {
        $result = $this->translator->translate('Test message', domain: 'test');
        $this->assertIsString($result);
    }

    public function testPluralTranslationValidation(): void
    {
        // Test that both singular and count are required
        $this->expectException(InvalidArgumentException::class);
        $this->translator->translate('message', singular: 'singular');
    }

    public function testCountValidation(): void
    {
        // Test that count must be numeric
        $this->expectException(InvalidArgumentException::class);
        $this->translator->translate('message', singular: 'singular', count: 'abc');
    }
}

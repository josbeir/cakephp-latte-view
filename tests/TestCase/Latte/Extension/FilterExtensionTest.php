<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Cake\Utility\Text;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\Latte\Extension\FilterExtension;
use LatteView\TestApp\View\AppView;
use LatteView\View\LatteView;

class FilterExtensionTest extends TestCase
{
    protected ?LatteView $view = null;

    protected ?Engine $latte = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->view = new AppView();
        $this->latte = $this->view->getEngine();
        $this->latte->setLoader(new StringLoader());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->latte = null;
        $this->view = null;
    }

    public function testFilter(): void
    {
        $compiled = $this->latte->compile('{=[1,2,3]|toList}');

        $this->assertStringContainsString('$this->filters->toList)([1, 2, 3])', $compiled);
    }

    public function testRender(): void
    {
        $view = new AppView();
        $latte = $view->getEngine();
        $rendered = $latte->renderToString('filters');

        $this->assertStringContainsString('This is the full text with some keyword in it', $rendered);
        $this->assertStringContainsString('This is a text with <span class="highlight">keyword</span>', $rendered);
        $this->assertStringContainsString('Some-Text-With-Spaces', $rendered);
        $this->assertStringContainsString('1, 2 and 3', $rendered);
        $this->assertStringContainsString('$123.46', $rendered);
        $this->assertStringContainsString('123.46', $rendered);
        $this->assertStringContainsString('11/29/73, 9:33 PM', $rendered);
        $this->assertStringContainsString('0.12%', $rendered);
        $this->assertStringContainsString('123 Bytes', $rendered);
        $this->assertStringContainsString('01-01-2025', $rendered);
        $this->assertStringContainsString('Jan 1, 2025, 2:30 PM', $rendered);
        $this->assertStringContainsString('1735741800', $rendered);
        $this->assertStringContainsString('2025-01-01T14:30:00+00:00', $rendered);
        $this->assertStringContainsString('Wed, 01 Jan 2025 14:30:00 +0000', $rendered);
        $this->assertStringContainsString('1735741800', $rendered);
    }

    public function testFilterArray(): void
    {
        $filters = $this->latte->getFilters();

        $this->assertArrayHasKey('toList', $filters);
        $this->assertArrayHasKey('currency', $filters);
        $this->assertArrayHasKey('camelize', $filters);
        $this->assertArrayHasKey('format', $filters);
    }

    public function testExtractFunctions(): void
    {
        $extension = new FilterExtension($this->view);
        $functions = $extension->extractFunctions(
            Text::class,
            blacklist: ['excerpt'],
            alias: ['truncate' => 'myTruncate'],
            returnsHtml: ['highlight'],
        );

        $this->assertIsArray($functions);
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('toList', $functions);
        $this->assertArrayNotHasKey('excerpt', $functions);

        $this->assertArrayHasKey('myTruncate', $functions);
        $this->assertArrayNotHasKey('truncate', $functions);

        $this->assertArrayHasKey('highlight', $functions);
        $this->assertIsCallable($functions['highlight']);
    }
}

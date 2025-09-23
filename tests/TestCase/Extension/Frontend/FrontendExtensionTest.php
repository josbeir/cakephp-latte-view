<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Extension\Frontend;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use LatteView\Extension\Frontend\FrontendExtension;

class FrontendExtensionTest extends TestCase
{
    protected ?View $view = null;

    protected ?FrontendExtension $extension = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->view = new View();
        $this->extension = new FrontendExtension($this->view);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->view = null;
        $this->extension = null;
    }

    public function testExtensionCreation(): void
    {
        $this->assertInstanceOf(FrontendExtension::class, $this->extension);
    }

    public function testDefaultFrameworkMappings(): void
    {
        $tags = $this->extension->getTags();

        $this->assertArrayHasKey('n:data', $tags);
        $this->assertArrayHasKey('n:data-alpine', $tags);
        $this->assertArrayHasKey('n:data-stimulus', $tags);
        $this->assertArrayHasKey('n:data-htmx', $tags);
    }

    public function testCustomFrameworkMappings(): void
    {
        $config = [
            'custom' => 'data-{name}-props',
            'vue' => ':data',
        ];

        $extension = new FrontendExtension($this->view, $config);
        $tags = $extension->getTags();

        $this->assertArrayHasKey('n:data-custom', $tags);
        $this->assertArrayHasKey('n:data-vue', $tags);
        $this->assertArrayHasKey('n:data-alpine', $tags); // Should still have defaults
    }

    public function testGetProviders(): void
    {
        $providers = $this->extension->getProviders();

        $this->assertArrayHasKey('cakeView', $providers);
        $this->assertSame($this->view, $providers['cakeView']);
    }

    public function testExtensionWithEmptyConfig(): void
    {
        $extension = new FrontendExtension($this->view, []);
        $tags = $extension->getTags();

        // Should still have default framework mappings
        $this->assertArrayHasKey('n:data-alpine', $tags);
        $this->assertArrayHasKey('n:data-stimulus', $tags);
        $this->assertArrayHasKey('n:data-htmx', $tags);
    }

    public function testJavaScriptModeFrameworkMappings(): void
    {
        $tags = $this->extension->getTags();

        // Test that -js variants are available
        $this->assertArrayHasKey('n:data-js', $tags);
        $this->assertArrayHasKey('n:data-alpine-js', $tags);
        $this->assertArrayHasKey('n:data-stimulus-js', $tags);
        $this->assertArrayHasKey('n:data-htmx-js', $tags);
    }

    public function testGetFunctions(): void
    {
        // Test that getFunctions() returns the json function
        // This covers lines 134-136 in FrontendExtension::getFunctions()

        $functions = $this->extension->getFunctions();

        $this->assertArrayHasKey('json', $functions);
        $this->assertIsCallable($functions['json']);

        // Test the json function
        $jsonFunction = $functions['json'];
        $result = $jsonFunction(['key' => 'value']);

        // Should be JavaScript-escaped JSON
        $this->assertStringContainsString('key', $result);
        $this->assertStringContainsString('value', $result);
    }
}

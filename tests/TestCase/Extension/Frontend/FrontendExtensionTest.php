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
}

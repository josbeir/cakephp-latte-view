<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Integration;

use Cake\TestSuite\TestCase;
use Cake\View\Exception\MissingTemplateException;
use Latte\Engine;
use Latte\Sandbox\SecurityPolicy;
use LatteView\TestApp\View\AppView;
use LatteView\View\LatteView;
use ReflectionClass;

class LatteViewIntegrationTest extends TestCase
{
    protected ?AppView $view = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->view = new AppView();
        $this->view->setConfig('cache', false);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->view = null;
    }

    public function testEngineInitialization(): void
    {
        $engine = $this->view->getEngine();

        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function testConfigOptions(): void
    {
        // Test cache configuration
        $this->view->setConfig('cache', true);
        $this->assertTrue($this->view->getConfig('cache'));

        // Test auto refresh
        $autoRefresh = $this->view->getAutoRefresh();
        $this->assertIsBool($autoRefresh);

        // Test sandbox policy
        $policy = $this->view->getSandboxPolicy();
        $this->assertInstanceOf(SecurityPolicy::class, $policy);
    }

    public function testCustomViewExtension(): void
    {
        $reflection = new ReflectionClass($this->view);
        $property = $reflection->getProperty('_ext');
        $property->setAccessible(true);

        $ext = $property->getValue($this->view);

        $this->assertSame('.latte', $ext);
    }

    public function testErrorHandling(): void
    {
        $this->expectException(MissingTemplateException::class);
        $this->view->render('non_existent_template');
    }

    public function testLatteViewInheritance(): void
    {
        $reflection = new ReflectionClass($this->view);
        $this->assertTrue($reflection->isSubclassOf(LatteView::class));
    }

    public function testViewSetters(): void
    {
        $this->view->setSandboxPolicy(new SecurityPolicy());
        $policy = $this->view->getSandboxPolicy();
        $this->assertInstanceOf(SecurityPolicy::class, $policy);
    }

    public function testEngineExtensions(): void
    {
        $engine = $this->view->getEngine();

        // Verify engine has extensions
        $this->assertInstanceOf(Engine::class, $engine);

        // Test that the engine can be configured
        $engine->setAutoRefresh(true);
        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function testViewConfiguration(): void
    {
        $defaultConfig = $this->view->getConfig();

        $this->assertIsArray($defaultConfig);
        $this->assertArrayHasKey('cache', $defaultConfig);
        $this->assertArrayHasKey('defaultHelpers', $defaultConfig);
    }

    public function testSandboxModeToggle(): void
    {
        $this->view->setConfig('sandbox', true);
        $engine = $this->view->getEngine();

        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function testCachePathConfiguration(): void
    {
        $cachePath = '/tmp/test_cache';
        $this->view->setConfig('cachePath', $cachePath);

        $this->assertSame($cachePath, $this->view->getConfig('cachePath'));
    }
}

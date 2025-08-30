<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\Latte\Loaders;

use Cake\TestSuite\TestCase;
use LatteView\Exception\TemplateNotFoundException;
use LatteView\Latte\Loaders\FileLoader;
use LatteView\TestPlugin\TestPlugin;
use PHPUnit\Framework\Attributes\DataProvider;

class FileLoaderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadPlugins([TestPlugin::class]);
    }

    public static function pathProvider(): array
    {
        return [
            [
                'simple',
                '/test_app/templates/simple.latte',
            ],
            [
                'simple.latte',
                '/test_app/templates/simple.latte',
            ],
            [
                '/simple',
                '/test_app/templates/simple.latte',
            ],
            [
                '/Page/home',
                '/test_app/templates/Page/home.latte',
            ],
            [
                '@Test.plugin.latte',
                '/test_app/plugins/Test/templates/plugin.latte',
            ],
            [
                '@Test.plugin',
                '/test_app/plugins/Test/templates/plugin.latte',
            ],
            [
                '@Test./layout/pluginlayout',
                '/test_app/plugins/Test/templates/layout/pluginlayout.latte',
            ],
        ];
    }

    #[DataProvider('pathProvider')]
    public function testGetContentWithValidFile(string $path, string $expectedPath): void
    {
        $loader = new FileLoader();

        $result = $loader->findTemplate($path);

        $this->assertStringEndsWith($expectedPath, $result);
    }

    public function testInvalidTemplate(): void
    {
        $loader = new FileLoader();

        $this->expectException(TemplateNotFoundException::class);
        $loader->findTemplate('invalid');
    }

    public function testInvalidPluginTemplate(): void
    {
        $loader = new FileLoader();

        $this->expectException(TemplateNotFoundException::class);
        $loader->findTemplate('@Test.invalid');
    }
}

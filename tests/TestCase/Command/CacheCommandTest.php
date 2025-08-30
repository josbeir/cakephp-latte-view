<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use LatteView\Command\CacheCommand;

class CacheCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected function setUp(): void
    {
        $cacheDir = CACHE . 'latte_view' . DS;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $cacheDir = CACHE . 'latte_view' . DS;
        if (is_dir($cacheDir)) {
            rmdir($cacheDir);
        }

        parent::tearDown();
    }

    public function testCacheClearCommand(): void
    {
        $this->exec('latte clear');

        $this->assertExitCode(CacheCommand::CODE_SUCCESS);
        $this->assertOutputContains('Cache directory cleared successfully.');
    }

    public function testInvalidClass(): void
    {
        $this->expectExceptionMessage('View class Plugin.View not found.');
        $this->exec('latte clear -c Plugin.View');
    }

    public function testNotSubclassOfView(): void
    {
        $this->expectExceptionMessage('Invalid view class. View class must be subclass of \LatteView\View\LatteView.');
        $this->exec('latte clear -c Other');
    }

    public function testInvalidDirectory(): void
    {
        $cacheDir = CACHE . 'latte_view' . DS;
        if (is_dir($cacheDir)) {
            rmdir($cacheDir);
        }

        $this->exec('latte clear');
        $this->assertErrorContains('Cache directory does not exist.');
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use LatteView\Command\CacheCommand;

class LinterCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    public function testLinter(): void
    {
        $this->exec('latte linter');
        $this->assertOutputContains('Done (checked');
        $this->assertExitCode(CacheCommand::CODE_SUCCESS);
    }

    public function testPlugin(): void
    {
        $this->exec('latte linter -p Test');
        $this->assertOutputContains('Done (checked');

        $this->assertExitCode(CacheCommand::CODE_SUCCESS);
    }

    public function testLinterConstants(): void
    {
        $this->assertSame(0, CacheCommand::CODE_SUCCESS);
        $this->assertSame(1, CacheCommand::CODE_ERROR);
    }
}

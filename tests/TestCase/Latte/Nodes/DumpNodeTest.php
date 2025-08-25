<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\Error\Debugger;
use Cake\TestSuite\TestCase;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

class DumpNodeTest extends TestCase
{
    public function testDumpNode(): void
    {
        $latte = (new AppView())->getEngine();
        $latte->setLoader(new StringLoader());

        $compiled = $latte->compile('{dump}');
        $this->assertStringContainsString(Debugger::class . '::printVar(get_defined_vars())', $compiled);
    }

    public function testDumpNodeWithVariable(): void
    {
        $latte = (new AppView())->getEngine();
        $latte->setLoader(new StringLoader());

        $compiled = $latte->compile('{$var = test}{dump $var}');
        $this->assertStringContainsString(Debugger::class . '::printVar($var)', $compiled);
    }
}

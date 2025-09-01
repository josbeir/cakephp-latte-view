<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

/**
 * @package LatteView
 */
class CellNodeTest extends TestCase
{
    protected ?Engine $latte = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->latte = (new AppView())->getEngine();
        $this->latte->setLoader(new StringLoader());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->latte = null;
    }

    /**
     * @package LatteView
     */
    public function testBasic(): void
    {
        $compiled = $this->latte->compile('{cell TestCell}');
        $expected = '$this->global->cakeView->cell(\'TestCell\', [],)->render()';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testArguments(): void
    {
        $compiled = $this->latte->compile('{cell TestCell "argumentValue", "argumentValue2", element: "myEl"}');
        $expected = '$this->global->cakeView->cell(\'TestCell\', [\'argumentValue\', \'argumentValue2\'],)->render(\'myEl\')';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testWithOptions(): void
    {
        $compiled = $this->latte->compile('{cell TestCell "argumentValue", "argumentValue2", options: ["option1", "option2"], element: \'bla\'}');
        $expected = '$this->global->cakeView->cell(\'TestCell\', [\'argumentValue\', \'argumentValue2\'], [\'option1\', \'option2\'])->render(\'bla\')';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testOutput(): void
    {
        $view = new AppView();
        $compiled = $view->render('cell');
        $this->assertStringContainsString('Cell argument: Testing', $compiled);
        $this->assertStringContainsString('Cell argument: First Second', $compiled);
    }
}

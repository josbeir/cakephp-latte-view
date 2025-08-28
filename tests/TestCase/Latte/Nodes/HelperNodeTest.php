<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

class HelperNodeTest extends TestCase
{
    protected ?AppView $view = null;

    protected ?Engine $latte = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->view = new AppView();
        $this->latte = (new AppView())->getEngine();
        $this->latte->setLoader(new StringLoader());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->view = null;
        $this->latte = null;
    }

    public function testMethodBuilding(): void
    {
        $compiled = $this->latte->compile("{Html link 'Label', '/'}");
        $expected = "global->cakeView->Html->{'link'}('Label', '/')";
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testMultiArgument(): void
    {
        $compiled = $this->latte->compile("{Form control 'myfield', label: 'test'}");
        $expected = "echo \$this->global->cakeView->Form->{'control'}('myfield', label: 'test');";
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testCustomHelper(): void
    {
        $compiled = $this->view->render('helpers');
        $this->assertStringContainsString('Hello from CustomHelper!', $compiled);
    }

    public function testRenderedHelper(): void
    {
        $compiled = $this->view->render('helpers');
        $this->assertStringContainsString('form method="post"', $compiled);
    }
}

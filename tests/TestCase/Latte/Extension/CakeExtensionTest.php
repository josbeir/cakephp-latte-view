<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

class CakeExtensionTest extends TestCase
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

    public function testHelperFunction(): void
    {
        $compiled = $this->latte->compile("{helper('Html')->link('Label', '/')}");
        $expected = '($this->global->fn->helper)($this, \'Html\'))';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testViewFunction(): void
    {
        $compiled = $this->latte->compile('{view()->getRequest()}');
        $expected = '$this->global->fn->view)($this, ))->getRequest()';
        $this->assertStringContainsString($expected, $compiled);
    }
}

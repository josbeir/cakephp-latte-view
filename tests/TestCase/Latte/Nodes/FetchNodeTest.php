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
class FetchNodeTest extends TestCase
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

    public function testFetch(): void
    {
        $compiled = $this->latte->compile("{fetch 'test'}");
        $expected = 'echo $this->global->cakeView->fetch(\'test\')';
        $this->assertStringContainsString($expected, $compiled);
    }
}

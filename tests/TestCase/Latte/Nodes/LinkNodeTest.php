<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

class LinkNodeTest extends TestCase
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

    public function testLinkNodeWithStringRoute(): void
    {
        $compiled = $this->latte->compile("{link 'Label' '/'}");
        $expected = 'echo $this->global->view->Html->link(\'Label\', \'/\');';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testLinkNodeWithArrayRoute(): void
    {
        $compiled = $this->latte->compile("{link 'Label' url: ['controller' => 'Pages', 'action' => 'display', 'home']}");
        $expected = 'echo $this->global->view->Html->link(\'Label\', url: [\'controller\' => \'Pages\', \'action\' => \'display\', \'home\']);';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testLinkNodeWithOptions(): void
    {
        $compiled = $this->latte->compile("{link 'Label' '/', options: [class: 'my-class']}");
        $expected = 'echo $this->global->view->Html->link(\'Label\', \'/\', options: [\'class\' => \'my-class\']);';
        $this->assertStringContainsString($expected, $compiled);
    }
}

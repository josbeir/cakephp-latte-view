<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Panel;

use Cake\TestSuite\TestCase;
use Cake\View\View;
use LatteView\Panel\LattePanel;
use LatteView\TestApp\View\AppView;

class LattePanelTest extends TestCase
{
    protected ?View $view = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadPlugins(['DebugKit']);
        $this->view = new AppView();
    }

    public function testData(): void
    {
        $panel = new LattePanel();
        $this->view->render('simple');

        $data = $panel->data();
        $this->assertArrayHasKey('templates', $data);
        $this->assertStringEndsWith('templates/simple.latte', $data['templates'][0]['name']);
        $this->assertStringEndsWith('templates/layout/default.latte', $data['templates'][1]['name']);
    }
}

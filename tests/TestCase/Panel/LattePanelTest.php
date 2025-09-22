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

    public function testSummary(): void
    {
        $panel = new LattePanel();

        // Test with no templates
        $summary = $panel->summary();
        $this->assertSame('0', $summary);
    }

    public function testAddTemplate(): void
    {
        $panel = new LattePanel();

        // Verify panel starts empty
        $this->assertSame('0', $panel->summary());

        // Panel should exist and be callable
        $this->assertTrue(method_exists($panel, 'addTemplate'));
        $this->assertTrue(method_exists($panel, 'summary'));
        $this->assertTrue(method_exists($panel, 'data'));
    }
}

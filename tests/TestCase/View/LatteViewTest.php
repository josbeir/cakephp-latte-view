<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Sandbox\SecurityPolicy;
use Latte\SecurityViolationException;
use LatteView\TestApp\View\AppView;

class LatteViewTest extends TestCase
{
    protected ?AppView $view;

    protected function setUp(): void
    {
        parent::setUp();

        $this->view = new AppView();
    }

    protected function tearDown(): void
    {
        $this->view = null;

        parent::tearDown();
    }

    public function testEngine(): void
    {
        $this->assertInstanceOf(Engine::class, $this->view->getEngine());
    }

    public function testRenderSimpleTemplate(): void
    {
        $output = $this->view->render('simple');

        $this->assertStringContainsString('<html lang="en">', $output);
        $this->assertStringContainsString('I Like Latte', $output);
    }

    public function testRenderSimpleTemplateNoLayout(): void
    {
        $output = $this->view->render('simple', false);

        $this->assertStringNotContainsString('<html lang="en">', $output);
        $this->assertStringContainsString('I Like Latte', $output);
    }

    public function testDisableAutoLayout(): void
    {
        $this->view->disableAutoLayout();
        $output = $this->view->render('simple');

        $this->assertStringNotContainsString('<html lang="en">', $output);
        $this->assertStringContainsString('I Like Latte', $output);
    }

    public function testCustomLayout(): void
    {
        $this->view->setLayout('custom');
        $output = $this->view->render('simple');

        $this->assertStringContainsString('I am custom !', $output);
        $this->assertStringContainsString('I Like Latte', $output);
    }

    public function testCustomLayoutFromTemplate(): void
    {
        $output = $this->view->render('extend');

        $this->assertStringContainsString('I am custom !', $output);
        $this->assertStringContainsString('Extended Template', $output);
        $this->assertStringContainsString('This is the extended template content.', $output);
    }

    public function testRenderTemplateWithData(): void
    {
        $this->view->set(['variable' => 'hello world']);
        $output = $this->view->render('variable');

        $this->assertStringContainsString('hello world', $output);
    }

    public function testDumpDebug(): void
    {
        $this->view->set(['variable' => 'hello world']);
        $output = $this->view->render('dump');

        // Bit difficult to test, but we can check for the presence of the variable
        $this->assertStringContainsString('hello world', $output);
    }

    public function testSandbox(): void
    {
        $this->view->setConfig('sandbox', true);

        $output = $this->view->render('simple');
        $this->assertStringContainsString('<html lang="en">', $output);
        $this->assertStringContainsString('I Like Latte', $output);
    }

    public function testSandboxRestrictive(): void
    {
        $this->view->setConfig('sandbox', true);
        $this->view->setSandboxPolicy(new SecurityPolicy());

        $this->expectException(SecurityViolationException::class);

        $this->view->set(['variable' => 'hello world']);
        $this->view->render('variable');
    }

    public function testGetAutoRefresh(): void
    {
        $this->assertTrue($this->view->getAutoRefresh());

        $this->view->setConfig('autoRefresh', false);
        $this->assertFalse($this->view->getAutoRefresh());

        Configure::write('debug', false);

        $this->view->setConfig('autoRefresh', null);
        $this->assertFalse($this->view->getAutoRefresh());

        $this->view->setConfig('autoRefresh', true);
        $this->assertTrue($this->view->getAutoRefresh());

        Configure::write('debug', true);
    }
}

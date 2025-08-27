<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\RuntimeException;
use Latte\Sandbox\SecurityPolicy;
use Latte\SecurityViolationException;
use LatteView\TestApp\View\AppView;
use LatteView\TestApp\View\Parameter\MyTemplateParams;

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

    public function testMultiBlockRendering(): void
    {
        $this->view->disableAutoLayout();
        $this->view->setConfig('blocks', ['block1', 'block2']);

        $output = $this->view->render('multi_block');

        $this->assertStringContainsString('Block 1 content', $output);
        $this->assertStringContainsString('Block 2 content', $output);
        $this->assertStringNotContainsString('Block 3 content', $output);
    }

    public function testUnknownBlockName(): void
    {
        $this->expectException(RuntimeException::class);

        $this->view->disableAutoLayout();
        $this->view->setConfig('blocks', ['unknown']);
        $this->view->render('multi_block');
    }

    public function testTranslations(): void
    {
        $view = new AppView();

        I18n::setLocale('en_US');
        $output = $view->render('translate');
        $this->assertStringContainsString('I like that color', $output);
        $this->assertStringContainsString('I organize code', $output);

        I18n::setLocale('en_GB');
        $output = $view->render('translate');
        $this->assertStringContainsString('I like that colour', $output);
        $this->assertStringContainsString('I organise code', $output);
        $this->assertStringContainsString('Hello from custom domain', $output);
        $this->assertStringContainsString('1 item', $output);
        $this->assertStringContainsString('2 items', $output);
        $this->assertStringContainsString('2 items with myarg', $output);
        $this->assertStringContainsString('I like that color from domain', $output);
    }

    public function testWithParameterClass(): void
    {
        $this->view->set(MyTemplateParams::class, [
            'name' => 'Custom Name',
            'additional' => 'Custom Additional',
            'items' => ['Item 1', 'Item 2'],
        ]);

        $output = $this->view->render('parameter_class');
        $this->assertStringContainsString('Data: Custom Name', $output);
        $this->assertStringContainsString('Additional: Custom Additional', $output);
        $this->assertStringContainsString('Item: Item 1', $output);
        $this->assertStringContainsString('Item: Item 2', $output);
    }

    public function testWithParameterClassInstance(): void
    {
        $parameter = new MyTemplateParams(
            name: 'Instance Name',
            additional: 'Instance Additional',
            items: ['Item A', 'Item B'],
        );
        $this->view->set('params', $parameter);

        $output = $this->view->render('parameter_class');
        $this->assertStringContainsString('Data: Instance Name', $output);
        $this->assertStringContainsString('Additional: Instance Additional', $output);
        $this->assertStringContainsString('Item: Item A', $output);
        $this->assertStringContainsString('Item: Item B', $output);
    }
}

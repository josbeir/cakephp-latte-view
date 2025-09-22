<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\Core\Configure;
use Cake\I18n\I18n;
use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use Latte\Runtime\Template;
use Latte\RuntimeException;
use Latte\Sandbox\SecurityPolicy;
use Latte\SecurityViolationException;
use LatteView\TestApp\View\AppView;
use LatteView\TestApp\View\Parameter\MyTemplateParams;
use ReflectionClass;

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

    public function testMultiFragmentRendering(): void
    {
        $this->view->disableAutoLayout();
        $this->view->setConfig('fragments', ['fragment1', 'fragment2']);

        $output = $this->view->render('multi_fragment');

        $this->assertStringContainsString('Fragment 1 content', $output);
        $this->assertStringContainsString('Fragment 2 content', $output);
        $this->assertStringNotContainsString('Fragment 3 content', $output);
    }

    public function testUnknownFragmentName(): void
    {
        $this->expectException(RuntimeException::class);

        $this->view->disableAutoLayout();
        $this->view->setConfig('fragments', ['unknown']);
        $this->view->render('multi_fragment');
    }

    public function testTranslations(): void
    {
        $view = new AppView();

        I18n::setDefaultFormatter('sprintf');

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

    public function testLocaleFunction(): void
    {
        $output = $this->view->render('translate');
        $this->assertStringContainsString('The current locale is: en_GB.', $output);

        I18n::setLocale('en_US');
        $output = $this->view->render('translate');
        $this->assertStringContainsString('The current locale is: en_US.', $output);
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
        $this->assertStringContainsString('Tag from parameter class: <strong>Hello from view!</strong>', $output);
        $this->assertStringContainsString('Currency: €1,000.00', $output);
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

    public function testFetch(): void
    {
        $this->view->assign('test', 'Hello world');
        $content = $this->view->render('fetch');
        $this->assertStringContainsString('<h1>Hello world</h1>', $content);
    }

    public function testRawphpEnabed(): void
    {
        $view = new AppView();
        $view->setConfig('rawphp', true);

        $latte = $view->getEngine();
        $latte->setLoader(new StringLoader());

        $output = $latte->compile("{php echo 'test';}");
        $this->assertStringContainsString("echo 'test';", $output);
    }

    public function testRawphpDisabled(): void
    {
        $view = new AppView();
        $view->setConfig('rawphp', false);

        $latte = $view->getEngine();
        $latte->setLoader(new StringLoader());

        $this->expectExceptionMessage("Unexpected ''test'', expecting end of tag in {php}");
        $latte->compile("{php echo 'test';}");
    }

    public function testLayoutLookupWithReferenceType(): void
    {
        $view = new AppView();
        $reflection = new ReflectionClass($view);
        $method = $reflection->getMethod('layoutLookup');
        $method->setAccessible(true);

        // Create a mock template with reference type
        $mockTemplate = $this->createMock(Template::class);
        $mockTemplate->method('getReferenceType')->willReturn('block');

        $result = $method->invoke($view, $mockTemplate);
        $this->assertNull($result);
    }

    public function testLayoutLookupWithoutReferenceType(): void
    {
        $view = new AppView();
        $view->setLayout('default'); // Use existing layout

        $reflection = new ReflectionClass($view);
        $method = $reflection->getMethod('layoutLookup');
        $method->setAccessible(true);

        // Create a mock template without reference type
        $mockTemplate = $this->createMock(Template::class);
        $mockTemplate->method('getReferenceType')->willReturn(null);

        $result = $method->invoke($view, $mockTemplate);
        $this->assertStringContainsString('default.latte', $result);
    }
}

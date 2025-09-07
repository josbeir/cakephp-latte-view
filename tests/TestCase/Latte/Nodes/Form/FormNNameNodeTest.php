<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\Form\TestForm;
use LatteView\TestApp\View\AppView;

/**
 * @package LatteView
 */
class FormNNameNodeTest extends TestCase
{
    protected ?AppView $view = null;

    protected ?Engine $latte = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRoutes();

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

    public function testCompiled(): void
    {
        $result = $this->latte->compile(<<<'XX'
            {var $var = null}
            <form n:context=$var method="get">Hi</form>
        XX);

        $formTag = '$this->global->cakeView->Form->create($var, $__c_form_args)';
        $this->assertStringContainsString($formTag, $result);

        $endTag = '</form>';
        $this->assertStringContainsString($endTag, $result);

        $contents = '>Hi<';
        $this->assertStringContainsString($contents, $result);
    }

    public function testCompiledComplex(): void
    {
        $result = $this->latte->compile(<<<'XX'
            {var $var = null}
            <form n:context=$var
                method="get"
                url="{['_name' => 'display']}"
                expression="{('foo'|capitalize)}"
            >Hi</form>
        XX);

        dd($result);

        $this->assertStringContainsString("'method' => 'get'", $result);
        $this->assertStringContainsString("'url' => ['_name' => 'display']", $result);
        $this->assertStringContainsString('\'expression\' => ($this->filters->capitalize)(\'foo\')', $result);
    }

    public function testTemplate(): void
    {
        $form = new TestForm();
        $form->setData(['name' => 'John Doe', 'email' => 'john@example.com', 'body' => 'Hello world!']);

        $this->view->set('form', $form);
        $result = $this->view->render('form');

        $formStart = '<form method="post" accept-charset="utf-8" action="/">';
        $this->assertStringContainsString($formStart, $result);

        $nameField = '<div class="input text"><label for="name">Name</label><input type="text" name="name" id="name" value="John Doe"></div>';
        $this->assertStringContainsString($nameField, $result);

        $emailField = '<div class="input email"><label for="email">Email</label><input type="email" name="email" id="email" value="john@example.com"></div>';
        $this->assertStringContainsString($emailField, $result);

        $bodyField = '<div class="input textarea"><label for="body">Body</label><textarea name="body" id="body" rows="5">Hello world!</textarea></div>';
        $this->assertStringContainsString($bodyField, $result);

        $formEnd = '</form>';
        $this->assertStringContainsString($formEnd, $result);
    }

    public function testNullForm(): void
    {
        $form = new TestForm();

        $this->view->set('form', $form);
        $result = $this->view->render('form');

        $formStart = '<form method="post" accept-charset="utf-8" action="/">';
        $this->assertStringContainsString($formStart, $result);

        $nullContext = 'Null context';
        $this->assertStringContainsString($nullContext, $result);

        $formEnd = '</form>';
        $this->assertStringContainsString($formEnd, $result);
    }

    public function testWithArguments(): void
    {
        $form = new TestForm();

        $this->view->set('form', $form);
        $result = $this->view->render('form');

        $formStart = '<form enctype="multipart/form-data" method="post" accept-charset="utf-8" class="test" action="/">';
        $this->assertStringContainsString($formStart, $result);

        $hiddenMethod = '<div style="display:none;"><input type="hidden" name="_method" value="POST"></div>';
        $this->assertStringContainsString($hiddenMethod, $result);

        $fileFormContent = 'File form';
        $this->assertStringContainsString($fileFormContent, $result);

        $formEnd = '</form>';
        $this->assertStringContainsString($formEnd, $result);
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

class FieldNNameNodeTest extends TestCase
{
    protected ?AppView $view = null;

    protected ?Engine $latte = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRoutes();

        $this->view = new AppView();
        $this->view->setConfig('cache', false);

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
            <input n:name="hello" />
            <control n:name="username" />
            <control n:name="user.company.name" label="Company name" />
            <select n:name="options" options="[1,2,3]" />
            <label n:name="mylabel">My Label</label>
            <textarea n:name="description" />
        XX);

        $this->assertStringContainsString('echo $this->global->cakeView->Form->input(\'hello\', []);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->control(\'username\', []);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->control(\'user.company.name\', [\'label\' => \'Company name\']);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->select(\'options\', [\'options\' => [1, 2, 3]]);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->textarea(\'description\', []);', $result);
    }

    public function testRendered(): void
    {
        $response = $this->view->render('form_nname');

        $this->assertStringContainsString('<label for="mylabel">My Label</label> END', $response);
        $this->assertStringContainsString('<textarea name="description" rows="5"></textarea> END', $response);
        $this->assertStringContainsString('<input', $response);
        $this->assertStringContainsString('<select', $response);
    }
}

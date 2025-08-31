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
            <textarea n:name="description"></textarea>
        XX);

        //dd($result);

        $this->assertStringContainsString('echo $__c_Form->input(\'hello\', []);', $result);
        $this->assertStringContainsString('echo $__c_Form->control(\'username\', []);', $result);
        $this->assertStringContainsString('echo $__c_Form->control(\'user.company.name\', [\'label\' => \'Company name\']);', $result);
        $this->assertStringContainsString('echo $__c_Form->select(\'options\', [\'options\' => [1, 2, 3]]);', $result);
        $this->assertStringContainsString('echo $__c_Form->textarea(\'description\', []);', $result);

        // Special label tag.
        $this->assertStringContainsString('echo $__c_Form->label(\'mylabel\', null, []);', $result);
        $this->assertStringContainsString('$__c_Form->resetTemplates();', $result);
        $this->assertStringContainsString("echo 'My Label</label>", $result);
    }

    public function testRendered(): void
    {
        $this->view->render('form_nname');

        $this->markTestIncomplete('Add some markup checks.');
    }
}

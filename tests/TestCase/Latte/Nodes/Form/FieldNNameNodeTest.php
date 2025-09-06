<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

/**
 * @package LatteView
 */
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
            {var $label = 'Its over Anakin.'}
            {var $label2 = 'I have the high ground.'}
            <input n:name="hello" />
            <control n:name="username" />
            <control n:name="user.company.name" label="Company name" />
            <select n:name="options, options: [1,2,3]" />
            <label n:name="mylabel">My Label</label>
            <label n:name="mylabel2">My Label <input n:name="test" /></label>
            <textarea n:name="starwars, label: $label" label="{$label2}" />
            <textarea n:name="description" />
        XX);

        $this->assertStringContainsString('echo $this->global->cakeView->Form->input(\'hello\', $__c_form_args);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->control(\'username\', $__c_form_args);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->control(\'user.company.name\', $__c_form_args);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->select(\'options\', $__c_form_args);', $result);
        $this->assertStringContainsString('echo $this->global->cakeView->Form->textarea(\'description\', $__c_form_args);', $result);
    }

    public function testAttributeParsing(): void
    {
        $result = $this->latte->compile(<<<'XX'
            {var $label = 'Its over Anakin.'}
            {var $label2 = 'I have the high ground.'}
            <input n:name="hello, label: ($label|uppercase)" label="{$label2}" />
            <input n:name="hello, label: $label" label="{$label2}" />
        XX);

        $this->assertStringContainsString(Hash::class . '::merge([\'label\' => ($this->filters->uppercase)($label)], [\'label\' => $label2]);', $result);
        $this->assertStringContainsString('Hash::merge([\'label\' => $label], [\'label\' => $label2]);', $result);
    }

    public function testRendered(): void
    {
        $response = $this->view->render('form_nname');

        $this->assertStringContainsString('<label for="mylabel">My Label</label> END', $response);
        $this->assertStringContainsString('<textarea name="description" rows="5"></textarea> END', $response);
        $this->assertStringContainsString('<input', $response);
        $this->assertStringContainsString('<select', $response);

        // wrapped label
        $this->assertStringContainsString('<label for="wrapped">', $response);
        $this->assertStringContainsString('<strong>Wrapped Label</strong>', $response);
        $this->assertStringContainsString('<input type="input" name="wrapped_input">', $response);
        $this->assertStringContainsString('</label>', $response);
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\CompileException;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

/**
 * @package LatteView
 */
class PostableNNameNodeTest extends TestCase
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
            <button n:post="[_name: 'display']">Hello world</button>
            <button n:post="[_name: 'display']" confirmMessage="Hi there!">Hello world</button>
            <a n:post="[_name: 'display'], confirmMessage: 'Hi'">Hello world</a>
        XX);

        $this->assertStringContainsString('cakeView->Form->postButton', $result);
        $this->assertStringContainsString('cakeView->Form->postLink', $result);
        $this->assertStringContainsString("array_merge([], ['confirmMessage' => 'Hi there!']", $result);
    }

    public function testInvalidTag(): void
    {
        $this->expectException(CompileException::class);
        $this->latte->compile(<<<'XX'
            <input n:post="[_name: 'display']">Hello world</input>
        XX);
    }

    public function testRendered(): void
    {
        $result = $this->view->render('postable');

        $this->assertStringContainsString('return false;">I\'m a postLink</a>', $result);
        $this->assertStringContainsString('return false;"><strong>Hello</strong></a>', $result);
        $this->assertStringContainsString('class="link"', $result);
        $this->assertStringContainsString('<button type="submit"><strong>Hello</strong></button>', $result);
        $this->assertStringContainsString('<button class="btn btn-primary" type="submit">Hi</button>', $result);
    }
}

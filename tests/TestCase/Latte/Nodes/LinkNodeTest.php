<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\TestApp\View\AppView;

/**
 * @package LatteView
 */
class LinkNodeTest extends TestCase
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

    public function testLinkNodeWithStringRoute(): void
    {
        $compiled = $this->latte->compile("{link '/'}");
        $this->assertStringContainsString(Router::class . '::url(\'/\', $__n_full)', $compiled);

        $compiled = $this->latte->compile('<a n:href="/">Hello</a>}');
        $this->assertStringContainsString(Router::class . '::url(\'/\', $__n_full)', $compiled);

        $compiled = $this->latte->compile('<a n:href="[_name: \'display\']">Hello</a>}');
        $this->assertStringContainsString(Router::class . '::url([\'_name\' => \'display\'], $__n_full)', $compiled);

        $compiled = $this->latte->compile('<a n:href="[_name: \'display\'], full: true">Hello</a>}');
        $this->assertStringContainsString('$__n_args = [\'full\' => true]', $compiled);

        $compiled = $this->latte->compile('<a n:named="display">Hello</a>}');
        $this->assertStringContainsString('$__n_params = array_merge([\'_name\' => \'display\'], $__n_args);', $compiled);
        $this->assertStringContainsString(Router::class . '::url($__n_params, $__n_full)', $compiled);
    }

    public function testRendered(): void
    {
        $rendered = $this->view->render('link');

        $this->assertStringContainsString('<span class="links-wrapper-1">http://localhost/</span>', $rendered);
        $this->assertStringContainsString('<span class="links-wrapper-2">http://localhost/display</span>', $rendered);

        $this->assertStringContainsString('<a href="/">Simple</a>', $rendered);
        $this->assertStringContainsString('<a href="http://localhost/">Hello from n:href full base</a>', $rendered);
        $this->assertStringContainsString('<a href="/display">Hello from n:named</a>', $rendered);
        $this->assertStringContainsString('<a href="/user">Hello from n:named full base</a>', $rendered);
        $this->assertStringContainsString('<a href="/user/1?page=1">Hello from n:named full</a>', $rendered);
    }
}

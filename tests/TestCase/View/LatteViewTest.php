<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\Core\Configure;
use Cake\TestSuite\TestCase;
use LatteView\TestApp\View\AppView;

class LatteViewTest extends TestCase
{
    protected AppView $view;

    protected function setUp(): void
    {
        parent::setUp();
        Configure::write('App.encoding', 'UTF-8');

        $this->view = new AppView();
    }

    /**
     * Test rendering simple twig template.
     */
    public function testRenderSimpleTemplate(): void
    {
        $output = $this->view->render('simple', false);
        $this->assertStringContainsString('I Like Latte', $output);
    }
}

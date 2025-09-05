<?php
declare(strict_types=1);

namespace LatteView\Tests\TestCase\View;

use Cake\TestSuite\TestCase;
use Latte\Engine;
use Latte\Loaders\StringLoader;
use LatteView\Latte\Extension\BaseExtension;
use LatteView\TestApp\View\AppView;
use LatteView\View\LatteView;

class BaseExtensionTest extends TestCase
{
    protected ?LatteView $view = null;

    protected ?Engine $latte = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->view = new AppView();
        $this->latte = $this->view->getEngine();
        $this->latte->setLoader(new StringLoader());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->latte = null;
        $this->view = null;
    }

    public function testViewFunction(): void
    {
        $compiled = $this->latte->compile('{view()->getRequest()}');
        $expected = '$this->global->fn->view)($this, ))->getRequest()';
        $this->assertStringContainsString($expected, $compiled);
    }

    public function testHelpers(): void
    {
        $extension = new BaseExtension($this->view);
        $tags = $extension->getTags();

        $expected = [
            'Breadcrumbs',
            'Flash',
            'Form',
            'Html',
            'Number',
            'Paginator',
            'Text',
            'Time',
            'Url',
            'Custom',
        ];

        foreach ($expected as $tag) {
            $this->assertArrayHasKey($tag, $tags);
        }
    }

    /**
     * This function tests if injected helper tags execute correctly.
     *
     * We do this by executing a helper function that is shared across all helpers.
     */
    public function testHelperExecution(): void
    {
        $extension = new BaseExtension($this->view);
        $helpers = $extension->helpers();
        $templates = ['default.latte' => ''];
        foreach (array_keys($helpers) as $helperName) {
            $templates[$helperName . '.latte'] = '{' . $helperName . ' initialize ["key" => "value"]}';
        }

        $latte = new Engine();
        $latte->setLoader(new StringLoader($templates));
        $latte->addExtension(new BaseExtension($this->view));

        foreach (array_keys($helpers) as $helperName) {
            $output = $latte->renderToString($helperName . '.latte');
            $this->assertStringContainsString('', $output);
        }
    }
}

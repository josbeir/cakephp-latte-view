<?php
declare(strict_types=1);

namespace LatteView\TestApp\View;

use LatteView\View\LatteView;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 */
class AppView extends LatteView
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     */
    public function initialize(): void
    {
    }
}

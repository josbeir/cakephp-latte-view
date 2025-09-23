<?php
declare(strict_types=1);

namespace LatteView\TestApp\View;

use LatteView\View\LatteView;

class AppView extends LatteView
{
    public function initialize(): void
    {
        $this->loadHelper('Custom');

        // Load Frontend Extension for testing
        $this->setConfig('extensions', [
            'frontend' => [],
        ]);
    }
}

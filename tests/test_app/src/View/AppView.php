<?php
declare(strict_types=1);

namespace LatteView\TestApp\View;

use LatteView\Extension\Frontend\FrontendExtension;
use LatteView\View\LatteView;

class AppView extends LatteView
{
    public function initialize(): void
    {
        $this->loadHelper('Custom');

        // Load Frontend Extension for testing
        $this->getEngine()->addExtension(new FrontendExtension($this));
    }
}

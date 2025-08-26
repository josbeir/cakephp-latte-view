<?php
declare(strict_types=1);

namespace LatteView\TestApp\View\Helper;

use Cake\View\Helper;

class CustomHelper extends Helper
{
    public function hello(): string
    {
        return 'Hello from CustomHelper!';
    }
}

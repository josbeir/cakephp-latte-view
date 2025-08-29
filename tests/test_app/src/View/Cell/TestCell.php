<?php
declare(strict_types=1);

namespace LatteView\TestApp\View\Cell;

use Cake\View\Cell;

class TestCell extends Cell
{
    public function display($argument = null, $argument2 = null): void
    {
        $this->set('argument', $argument);
        $this->set('argument2', $argument2);
    }
}

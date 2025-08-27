<?php
declare(strict_types=1);

namespace LatteView\TestApp\View\Parameter;

use LatteView\View\ParameterInterface;

class MyTemplateParams implements ParameterInterface
{
    public function __construct(
        public string $name = 'Default Name',
        public string $additional = 'Default Additional',
        public ?array $items = null,
    ) {
    }
}

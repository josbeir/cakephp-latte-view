<?php
declare(strict_types=1);

namespace LatteView\TestApp\View\Parameter;

use Latte\Attributes\TemplateFilter;
use Latte\Attributes\TemplateFunction;
use Latte\Runtime\Html;
use LatteView\View\Parameters;

class MyTemplateParams extends Parameters
{
    public function __construct(
        public string $name = 'Default Name',
        public string $additional = 'Default Additional',
        public ?array $items = null,
    ) {
    }

    /**
     * A generator that yields the item count from a helper.
     */
    #[TemplateFunction]
    public function tag(): Html
    {
        $result = $this->getView()->Html->tag('strong', 'Hello from view!');

        return new Html($result);
    }

    #[TemplateFilter]
    public function currency(string|float $number, ?string $currency = 'EUR'): string
    {
        return $this->getView()->Number->currency($number, $currency);
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Cake\I18n\Number;
use Cake\Utility\Inflector;
use Cake\Utility\Text;
use Cake\View\View;
use Latte\Extension;
use Latte\Runtime\Html;
use ReflectionClass;
use ReflectionMethod;

final class FilterExtension extends Extension
{
    /**
     * FilterExtension constructor.
     */
    public function __construct(
        protected View $view,
        protected array $existingFilters = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getFilters(): array
    {
        $textFilters = $this->extractFunctions(
            Text::class,
            blacklist: [
                'isMultibyte',
            ],
            returnsHtml: [
                'highlight',
            ],
        );

        $numberFilters = $this->extractFunctions(
            Number::class,
            blacklist: [
                'config',
                'format',
            ],
        );

        $inflectorFilters = $this->extractFunctions(
            Inflector::class,
            blacklist: [
                'reset',
                'rules',
            ],
            alias: [
                'variable' => 'iVariable',
            ],
        );

        return [
            ...$textFilters,
            ...$numberFilters,
            ...$inflectorFilters,
            ...$this->dateFilters(),
        ];
    }

    /**
     * Get date filters
     */
    public function dateFilters(): array
    {
        /** @var \Cake\View\Helper\TimeHelper $timeHelper */
        $timeHelper = $this->view->Time;

        $helperMethods = [
            'format',
            'i18nFormat',
            'nice',
            'toUnix',
            'toAtom',
            'toRss',
            'timeAgoInWords',
            'gmt',
        ];

        $filters = [];
        foreach ($helperMethods as $method) {
            $filters[$method] = fn(...$args) => $timeHelper->{$method}(...$args);
        }

        return $filters;
    }

    /**
     * Extract public functions from a class and return filter map
     *
     * @param class-string $class The class name to extract functions from.
     * @param array $blacklist A list of function names to exclude.
     * @param array $alias A map of function names to their aliases.
     * @param array $returnsHtml A list of function names that return HTML and should be wrapped in Html objects.
     * @return array A map of function names to their fully qualified names.
     */
    public function extractFunctions(
        string $class,
        array $blacklist = [],
        array $alias = [],
        array $returnsHtml = [],
    ): array {
        $filters = [];
        $reflection = new ReflectionClass($class);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC);

        foreach ($methods as $method) {
            $name = $method->getName();
            if (
                array_key_exists($name, $this->existingFilters)
                || str_starts_with($name, 'get')
                || str_starts_with($name, 'set')
                || str_starts_with($name, '_')
                || in_array($name, $blacklist)
            ) {
                continue;
            }

            $filterName = $alias[$name] ?? $name;

            if (in_array($name, $returnsHtml)) {
                $filters[$filterName] = fn(...$args): Html => new Html($class::{$name}(...$args));
                continue;
            }

            $filters[$filterName] = $class . '::' . $name;
        }

        return $filters;
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Panel;

use DebugKit\DebugPanel;
use Latte\Runtime\Template;
use ReflectionObject;

class LattePanel extends DebugPanel
{
    public string $plugin = 'LatteView';

    public static array $templates = [];

    public array $list = [];

    /**
     * Add a template to the panel.
     */
    public static function addTemplate(Template $template): void
    {
        static::$templates[] = $template;
    }

    /**
     * @inheritDoc
     */
    public function data(): array
    {
        if (static::$templates) {
            $this->buildList(static::$templates[0]);
        }

        return [
            'templates' => $this->list,
        ];
    }

    /**
     * Build the template list.
     */
    protected function buildList(Template $template, int $depth = 0, int $count = 1): void
    {
        $this->list[] = [
            'depth' => $depth,
            'count' => $count,
            'referenceType' => $template->getReferenceType(),
            'name' => $template->getName(),
            'phpFile' => (new ReflectionObject($template))->getFileName(),
        ];

        $children = [];
        $counter = [];
        foreach (static::$templates as $t) {
            if ($t->getReferringTemplate() === $template) {
                $name = $t->getName();
                $children[$name] = $t;
                $counter[$name] = ($counter[$name] ?? 0) + 1;
            }
        }

        foreach ($children as $name => $t) {
            $this->buildList($t, $depth + 1, $counter[$name]);
        }
    }
}

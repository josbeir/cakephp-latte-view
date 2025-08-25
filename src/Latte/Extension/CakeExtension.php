<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Cake\Routing\Router;
use Cake\View\Helper;
use Cake\View\View;
use Latte\Extension;
use LatteView\Latte\Nodes\DumpNode;
use LatteView\Latte\Nodes\LinkNode;

final class CakeExtension extends Extension
{
    /**
     * CakeExtension constructor.
     */
    public function __construct(
        protected ?View $view = null,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getProviders(): array
    {
        return [
            'view' => $this->view,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return [
            'dump' => DumpNode::create(...),
            'debug' => DumpNode::create(...),
            'link' => LinkNode::create(...),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFunctions(): array
    {
        return [
            'debug' => debug(...),
            'view' => fn(): ?View => $this->view,
            'helper' => fn(string $name): ?Helper => $this->view->{$name},
            'url' => Router::url(...),
            'rurl' => Router::reverse(...),
            '__' => fn(...$args) => __(...$args),
            '__d' => fn(...$args) => __d(...$args),
            '__dn' => fn(...$args) => __dn(...$args),
            '__n' => fn(...$args) => __n(...$args),
        ];
    }
}

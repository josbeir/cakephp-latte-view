<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\View\View;
use Latte\Compiler\Tag;
use Latte\Extension;
use LatteView\Latte\Nodes\DumpNode;
use LatteView\Latte\Nodes\FetchNode;
use LatteView\Latte\Nodes\HelperNode;
use LatteView\Latte\Nodes\LinkNode;

final class CakeExtension extends Extension
{
    /**
     * CakeExtension constructor.
     */
    public function __construct(
        protected View $view,
    ) {
    }

    /**
     * Initialize the list of helper helper names.
     */
    public function helpers(): array
    {
        $names = $this->view->getConfig('defaultHelpers', []);
        $defined_names = $this->view->helpers();

        foreach ($defined_names as $name => $helper) {
            $names[] = $name;
        }

        foreach ($names as $name) {
            $tags[$name] = fn(Tag $tag): HelperNode => HelperNode::create($name, $tag);
        }

        return $tags;
    }

    /**
     * @inheritDoc
     */
    public function getProviders(): array
    {
        return [
            'cakeView' => $this->view,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        $tags = [
            'dump' => DumpNode::create(...),
            'debug' => DumpNode::create(...),
            'link' => LinkNode::create(...),
            'fetch' => FetchNode::create(...),
        ];

        return array_merge($tags, $this->helpers());
    }

    /**
     * @inheritDoc
     */
    public function getFunctions(): array
    {
        return [
            'debug' => debug(...),
            'view' => fn(): View => $this->view,
            'request' => fn(): ServerRequest => $this->view->getRequest(),
            'url' => Router::url(...),
            'rurl' => Router::reverse(...),
        ];
    }
}

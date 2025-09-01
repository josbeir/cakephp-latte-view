<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\View\Helper;
use Cake\View\View;
use Latte\Compiler\Tag;
use Latte\Extension;
use LatteView\Latte\Nodes\CellNode;
use LatteView\Latte\Nodes\DumpNode;
use LatteView\Latte\Nodes\FetchNode;
use LatteView\Latte\Nodes\Form\FieldNNameNode;
use LatteView\Latte\Nodes\Form\FormNContextNode;
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
        $names = [];
        $tags = [];
        foreach ($this->view->helpers() as $helperName => $helper) {
            $names[] = $helperName;
        }

        $helpers = $this->view->getConfig('defaultHelpers', []);
        $helpers = array_merge($helpers, $names);

        foreach ($helpers as $name) {
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
            'fetch' => FetchNode::create(...),
            'cell' => CellNode::create(...),
            'link' => LinkNode::create(...),
            'n:href' => LinkNode::create(...),
            'n:named' => LinkNode::create(...),
            'n:context' => FormNContextNode::create(...),
            'n:name' => FieldNNameNode::create(...),
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
            'helper' => fn(string $name): ?Helper => $this->view->helpers()->{$name} ?? null,
            'request' => fn(): ServerRequest => $this->view->getRequest(),
            'url' => Router::url(...),
            'rurl' => Router::reverse(...),
        ];
    }
}

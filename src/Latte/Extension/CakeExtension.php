<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\View\View;
use Latte\Compiler\Tag;
use Latte\Extension;
use LatteView\Latte\Nodes\DumpNode;
use LatteView\Latte\Nodes\HelperNode;
use LatteView\Latte\Nodes\LinkNode;

final class CakeExtension extends Extension
{
    /**
     * List of helper names to be registered.
     */
    protected array $helperNames = [
        'Breadcrumbs',
        'Flash',
        'Form',
        'Html',
        'Number',
        'Paginator',
        'Text',
        'Time',
        'Url',
    ];

    /**
     * CakeExtension constructor.
     */
    public function __construct(protected View $view)
    {
        foreach ($this->view->helpers() as $name => $helper) {
            $this->helperNames[] = $name;
        }
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
        ];

        foreach ($this->helperNames as $helperName) {
            $tagName = 'c' . ucfirst($helperName);
            $tags[$tagName] = fn(Tag $tag): HelperNode => HelperNode::create($helperName, $tag);
        }

        return $tags;
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

<?php
declare(strict_types=1);

namespace LatteView\Latte\Extension;

use Latte\Extension;
use LatteView\Latte\Nodes\DumpNode;

final class CakeExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function getTags(): array
    {
        return [
            'dump' => [DumpNode::class, 'create'],
            'debug' => [DumpNode::class, 'create'],
        ];
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Generator;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class FetchNode extends StatementNode
{
    protected ?ExpressionNode $name = null;

    /**
     * Constructor.
     */
    public static function create(Tag $tag): static
    {
        $node = new self();

        if (!$tag->parser->isEnd()) {
            $node->name = $tag->parser->parseExpression();
        }

        return $node;
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        if ($this->name instanceof ExpressionNode) {
            yield $this->name;
        }
    }

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo $this->global->cakeView->fetch(%node);',
            $this->name,
        );
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Generator;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class LinkNode extends StatementNode
{
    protected ?ExpressionNode $title = null;

    protected ?ArrayNode $arguments = null;

    /**
     * Constructor.
     */
    public static function create(Tag $tag): static
    {
        $node = new self();

        if (!$tag->parser->isEnd()) {
            $node->title = $tag->parser->parseExpression();
            $node->arguments = $tag->parser->parseArguments();
        }

        return $node;
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        if ($this->title instanceof ExpressionNode) {
            yield $this->title;
        }
    }

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo $this->global->view->Html->link(%node, %args);',
            $this->title,
            $this->arguments,
        );
    }
}

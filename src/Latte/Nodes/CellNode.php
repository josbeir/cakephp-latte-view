<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Generator;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class CellNode extends StatementNode
{
    protected ?ExpressionNode $name = null;

    protected ?ArrayNode $arguments = null;

    /**
     * Constructor.
     */
    public static function create(Tag $tag): static
    {
        $node = new self();

        if (!$tag->parser->isEnd()) {
            $node->name = $tag->parser->parseExpression();
            $node->arguments = $tag->parser->parseArguments();
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
        $options = null;
        $element = null;
        foreach ($this->arguments->items as $key => $arg) {
            if ($arg->key == 'element') {
                $element = $arg->value;
                unset($this->arguments->items[$key]);
            }

            if ($arg->key == 'options') {
                $options = $arg->value;
                unset($this->arguments->items[$key]);
            }
        }

        return $context->format(
            'echo $this->global->cakeView->cell(%node, %node, %node)->render(%node);',
            $this->name,
            $this->arguments,
            $options,
            $element,
        );
    }
}

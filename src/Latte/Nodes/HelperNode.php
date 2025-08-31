<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Generator;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class HelperNode extends StatementNode
{
    protected ?ExpressionNode $method = null;

    protected ?ArrayNode $arguments = null;

    protected ?string $helperName;

    /**
     * Constructor.
     */
    public static function create(string $helperName, Tag $tag): static
    {
        $node = new self();

        $node->helperName = $helperName;

        if (!$tag->parser->isEnd()) {
            $node->method = $tag->parser->parseUnquotedStringOrExpression();
            $node->arguments = $tag->parser->parseArguments();
        }

        return $node;
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        if ($this->method instanceof ExpressionNode) {
            yield $this->method;
        }
    }

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo $this->global->cakeView->%raw->{%node}(%args) %line;',
            $this->helperName,
            $this->method,
            $this->arguments,
            $this->position,
        );
    }
}

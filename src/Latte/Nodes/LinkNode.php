<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Cake\Routing\Router;
use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class LinkNode extends StatementNode
{
    protected ExpressionNode $location;

    protected AreaNode $content;

    protected ArrayNode $args;

    protected ModifierNode $modifier;

    protected string $mode;

    /**
     * @inheritDoc
     */
    public static function create(Tag $tag): ?static
    {
        $tag->outputMode = $tag::OutputKeepIndentation;
        $tag->expectArguments();

        $node = new self();
        $node->location = $tag->parser->parseUnquotedStringOrExpression();

        $tag->parser->stream->tryConsume(',');
        $node->args = $tag->parser->parseArguments();
        $node->mode = $tag->name;
        $node->modifier = $tag->parser->parseModifier();
        $node->modifier->escape = true;
        $node->modifier->check = false;
        $node->mode = $tag->name;

        if ($tag->isNAttribute()) {
            // move at the beginning
            $node->position = $tag->position;
            array_unshift($tag->htmlElement->attributes->children, $node);

            return null;
        }

        return $node;
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        yield $this->location;
    }

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        $full = <<<'XX'
            $__n_args = %node;
            $__n_full = $__n_args['full'] ?? false;
        XX;

        if ($this->mode === 'href') {
            $context->beginEscape()->enterHtmlAttribute(null);
            $res = $context->format(
                $full .
                <<<'XX'
                    $__n_link = \Cake\Routing\Router::url(%node, $__n_full);
                    echo ' href="'; echo %modify($__n_link) %line; echo '"';
                XX,
                $this->args,
                $this->location,
                $this->modifier,
                $this->position,
            );
            $context->restoreEscape();

            return $res;

        // Named routing.
        } elseif ($this->mode === 'named') {
            if (!$this->location instanceof StringNode) {
                throw new CompileException('n:named only supports string literal as the route name.');
            }

            $context->beginEscape()->enterHtmlAttribute(null);
            $res = $context->format(
                $full .
                <<<'XX'
                    $__n_params = array_merge(['_name' => %node], $__n_args);
                    $__n_link = \Cake\Routing\Router::url($__n_params, $__n_full);
                    echo ' href="'; echo %modify($__n_link) %line; echo '"';
                XX,
                $this->args,
                $this->location,
                $this->modifier,
                $this->position,
            );
            $context->restoreEscape();

            return $res;
        }

        return $context->format(
            $full .
            ('echo %modify(' . Router::class . '::url(%node, $__n_full)) %line;'),
            $this->args,
            $this->modifier,
            $this->location,
            $this->position,
        );
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes\Form;

use Generator;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\FragmentNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use LatteView\Latte\Nodes\AttributeParserTrait;

/**
 * Provides <form n:context> support.
 */
final class FormNContextNode extends StatementNode
{
    use AttributeParserTrait;

    public ExpressionNode $context;

    public AreaNode $content;

    /**
     * Create a new FormNContextNode instance.
     */
    public static function create(Tag $tag): Generator
    {
        $tag->expectArguments();
        $node = new self();
        $tag->node = $node;
        $node->context = $tag->parser->parseExpression();

        [$node->content] = yield;
        $node->init($tag);

        return $node;
    }

    /**
     * Decorate the tag.
     */
    protected function init(Tag $tag): void
    {
        $el = $tag->htmlElement;
        $attributes = $this->getAttributesNode($el);

        $el->captureTagName = true;
        $el->tagNode = new AuxiliaryNode(fn(PrintContext $context): string => $context->format(
            'echo $this->global->cakeView->Form->create(%node, %node) %line;',
            $this->context,
            $attributes,
            $this->position,
        ));

        $el->attributes = null;

        // @phpstan-ignore-next-line
        $el->content = new FragmentNode([
            $el->content,
            new AuxiliaryNode(fn(PrintContext $context): string => $context->format(
                'echo $this->global->cakeView->Form->end() %line;',
                $this->position,
            )),
        ]);
    }

    /**
     * Print the node.
     */
    public function print(PrintContext $context): string
    {
        return $context->format(
            '%node %line',
            $this->content,
            $this->position,
        );
    }

    /**
     * Get the iterator.
     */
    public function &getIterator(): Generator
    {
        yield $this->context;
        yield $this->content;
    }
}

<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes\Form;

use Generator;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\FragmentNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
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

    protected ExpressionNode $context;

    protected AreaNode $content;

    protected ArrayNode $args;

    /**
     * Create a new FormNContextNode instance.
     */
    public static function create(Tag $tag): Generator
    {
        $tag->expectArguments();

        $node = new self();
        $node->context = $tag->parser->parseExpression();

        $tag->parser->stream->tryConsume(',');
        $node->args = $tag->parser->parseArguments();

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
            <<<'XX'
                $__c_form_args = \Cake\Utility\Hash::merge(%node, %node);
                echo $this->global->cakeView->Form->create(%node, $__c_form_args) %line;
            XX,
            $this->args,
            $attributes,
            $this->context,
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

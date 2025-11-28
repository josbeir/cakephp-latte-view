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
 * <input n:name>, <select n:name>, <textarea n:name>, <label n:name> and <button n:name>
 */
final class FieldNNameNode extends StatementNode
{
    use AttributeParserTrait;

    protected ExpressionNode $name;

    protected AreaNode $content;

    protected ArrayNode $args;

    /**
     * @inheritDoc
     */
    public static function create(Tag $tag): Generator
    {
        $tag->outputMode = Tag::OutputKeepIndentation;
        $tag->expectArguments();

        $node = new self();
        $node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);

        $tag->parser->stream->tryConsume(',');
        $node->args = $tag->parser->parseArguments();

        [$node->content] = yield;

        $node->init($tag);

        return $node;
    }

    /**
     * @inheritDoc
     */
    public function print(PrintContext $context): string
    {
        return $this->content->print($context);
    }

    /**
     * Decorate the tag.
     */
    private function init(Tag $tag): void
    {
        $el = $tag->htmlElement;
        $elName = strtolower($el->name);
        $attributes = $this->getAttributesNode($el);

        $print_config = [
            <<<'XX'
                $__c_form_args = \Cake\Utility\Hash::merge(%node, %node);
                echo $this->global->cakeView->Form->%raw(%node, $__c_form_args) %line;
            XX,
            $this->args,
            $attributes,
            $elName,
            $this->name,
            $this->position,
        ];

        if ($elName === 'label') {
            $print_config = [
                <<<'XX'
                    ob_start(); %node $__c_form_label = ob_get_clean();
                    $__c_form_args = array_merge(%node, %node, ['escape' => false]);
                    echo $this->global->cakeView->Form->%raw(%node, $__c_form_label, $__c_form_args) %line;
                XX,
                $el->content,
                $this->args,
                $attributes,
                $elName,
                $this->name,
                $this->position,
            ];
        }

        $el->selfClosing = true;
        $el->content = null;
        $el->attributes = new FragmentNode();
        $el->dynamicTag = new AuxiliaryNode(
            fn(PrintContext $context): string => $context->format(...$print_config),
        );
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        yield $this->name;
        yield $this->content;
    }
}

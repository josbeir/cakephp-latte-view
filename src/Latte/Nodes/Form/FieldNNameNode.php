<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes\Form;

use Generator;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
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

    public ExpressionNode $name;

    public ?ExpressionNode $part = null;

    public AreaNode $content;

    /**
     * @inheritDoc
     */
    public static function create(Tag $tag): Generator
    {
        $tag->expectArguments();
        $node = new self();
        $tag->node = $node;
        $node->name = $tag->parser->parseUnquotedStringOrExpression(colon: false);

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

        $pre = '$__c_Form = $this->global->cakeView->Form;';

        $print_config = [
            $pre . 'echo $__c_Form->%raw(%node, %node) %line;',
            $elName,
            $this->name,
            $attributes,
            $this->position,
        ];

        if ($elName === 'label') {
            $print_config = [
                <<<'XX'
                    $__c_Form->setTemplates([
                        'label' => '<label{{attrs}}>',
                    ]);
                    echo $__c_Form->%raw(%node, null, %node) %line;
                    $__c_Form->resetTemplates();
                XX,
                $elName,
                $this->name,
                $attributes,
                $this->position,
            ];
        } elseif (in_array($elName, ['control', 'select', 'textarea'])) {
            $el->captureTagName = true;
            $el->selfClosing = true;
            $el->content = null;
        }

        $el->tagNode = new AuxiliaryNode(
            fn(PrintContext $context): string => $context->format(...$print_config),
        );

        $el->attributes = null;
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

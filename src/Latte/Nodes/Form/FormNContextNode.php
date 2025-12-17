<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes\Form;

use Generator;
use Latte\Compiler\NodeHelpers;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\AuxiliaryNode;
use Latte\Compiler\Nodes\FragmentNode;
use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Html\ExpressionAttributeNode;
use Latte\Compiler\Nodes\Php\ArgumentNode;
use Latte\Compiler\Nodes\Php\ArrayItemNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Expression\FunctionCallNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\NameNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use LatteView\Extension\Frontend\Nodes\DataSerializationNode;
use LatteView\Extension\Frontend\Serializers\UniversalSerializer;
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

        if (!$el instanceof ElementNode) {
            return;
        }

        // Create a temporary context for attribute compilation
        $tempContext = new PrintContext();

        // Process attributes and separate them into:
        // 1. Attributes that can be passed to Form->create() (parsed successfully)
        // 2. Attributes that should remain on the element (complex/unparseable)
        $preservedAttributeNodes = [];
        $formAttributes = []; // ArrayItemNode[]

        foreach ($el->attributes->children as $child) {
            // Handle DataSerializationNode (e.g., n:data-alpine="$data")
            if ($child instanceof DataSerializationNode) {
                $attrName = $child->getPublicAttributeName();
                $nameNode = new StringNode($attrName);

                // Create function call: UniversalSerializer::serialize($data)
                $valueNode = new FunctionCallNode(
                    new NameNode(UniversalSerializer::class . '::serialize'),
                    [new ArgumentNode($child->getDataExpression(), false, false, null, $child->position)],
                );

                $formAttributes[] = new ArrayItemNode($valueNode, $nameNode);
                continue;
            }

            // Handle standard AttributeNode (e.g., method="get", hx-post="{$path}")
            if ($child instanceof AttributeNode) {
                $nameText = NodeHelpers::toText($child->name);
                if ($nameText !== null) {
                    $val = $el->getAttribute($nameText);
                    $parsed = $this->parseAttributeValue($val, $tempContext);

                    if (!$parsed instanceof ExpressionNode) {
                        // Complex attribute (e.g., x-data with JavaScript) - preserve on element
                        $preservedAttributeNodes[] = $child;
                    } else {
                        // Simple attribute - add to Form->create() args
                        $nameNode = new StringNode($nameText);
                        $formAttributes[] = new ArrayItemNode($parsed, $nameNode);
                    }
                }

                continue;
            }

            // Handle ExpressionAttributeNode (e.g., url="{['_name' => 'display']}")
            if ($child instanceof ExpressionAttributeNode) {
                $nameNode = new StringNode($child->name);
                $formAttributes[] = new ArrayItemNode($child->value, $nameNode);
                continue;
            }
        }

        // Build the attributes array for Form->create()
        $attributes = new ArrayNode($formAttributes);

        $el->dynamicTag = new AuxiliaryNode(fn(PrintContext $context): string => $context->format(
            <<<'XX'
                $__c_form_args = \Cake\Utility\Hash::merge(%node, %node);
                echo $this->global->cakeView->Form->create(%node, $__c_form_args) %line;
            XX,
            $this->args,
            $attributes,
            $this->context,
            $this->position,
        ));

        // Preserve complex attributes on the form element
        $el->attributes = new FragmentNode($preservedAttributeNodes);

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

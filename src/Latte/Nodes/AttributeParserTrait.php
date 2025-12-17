<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Latte\Compiler\Nodes\AreaNode;
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
use Latte\Compiler\Nodes\PrintNode;
use Latte\Compiler\Nodes\TextNode;
use LatteView\Extension\Frontend\Nodes\DataSerializationNode;
use LatteView\Extension\Frontend\Serializers\UniversalSerializer;

trait AttributeParserTrait
{
    /**
     * Get the attributes node.
     */
    protected function getAttributesNode(?ElementNode $el): ArrayNode
    {
        if (!$el instanceof ElementNode) {
            return new ArrayNode([]);
        }

        $items = [];
        foreach ($el->attributes->children as $child) {
            // Handle DataSerializationNode (e.g., n:data-alpine="$data")
            if ($child instanceof DataSerializationNode) {
                $attrName = $child->getPublicAttributeName();
                $nameNode = new StringNode($attrName);

                // Create a function call expression: UniversalSerializer::serialize($data)
                $valueNode = new FunctionCallNode(
                    new NameNode(UniversalSerializer::class . '::serialize'),
                    [new ArgumentNode($child->getDataExpression(), false, false, null, $child->position)],
                );

                $items[] = new ArrayItemNode($valueNode, $nameNode);
                continue;
            }

            // Handle standard AttributeNode (e.g., method="get")
            if ($child instanceof AttributeNode && $child->name instanceof TextNode) {
                $name = $child->name->content;
                $val = $el->getAttribute($name);
                $nameNode = new StringNode($name);

                $valueNode = $this->parseAttributeValue($val);
                if ($valueNode !== null) {
                    $items[] = new ArrayItemNode($valueNode, $nameNode);
                }

                continue;
            }

            // Handle ExpressionAttributeNode (e.g., url="{['_name' => 'display']}")
            if ($child instanceof ExpressionAttributeNode) {
                $nameNode = new StringNode($child->name);
                $valueNode = $child->value;
                $items[] = new ArrayItemNode($valueNode, $nameNode);
                continue;
            }
        }

        return new ArrayNode($items);
    }

    /**
     * Parse an attribute value into an ExpressionNode.
     */
    protected function parseAttributeValue(mixed $val): ?ExpressionNode
    {
        // Handle PrintNode (expression like {$var})
        if ($val instanceof PrintNode) {
            return $val->expression;
        }

        // Handle FragmentNode (may contain PrintNode children)
        // If fragment has a single PrintNode child, extract its expression
        if ($val instanceof FragmentNode && (count($val->children) === 1 && $val->children[0] instanceof PrintNode)) {
            return $val->children[0]->expression;
        }

        // Handle AreaNode that might contain a PrintNode
        if ($val instanceof AreaNode) {
            // Try to find a PrintNode in the tree
            $printNode = $this->findPrintNode($val);
            if ($printNode !== null) {
                return $printNode->expression;
            }
        }

        // Handle plain string values
        if (is_string($val)) {
            return new StringNode($val);
        }

        return null;
    }

    /**
     * Find a PrintNode in an AreaNode tree.
     */
    protected function findPrintNode(AreaNode $node): ?PrintNode
    {
        if ($node instanceof PrintNode) {
            return $node;
        }

        if ($node instanceof FragmentNode) {
            foreach ($node->children as $child) {
                $found = $this->findPrintNode($child);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}

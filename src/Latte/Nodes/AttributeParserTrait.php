<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ArrayItemNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\PrintNode;
use Latte\Compiler\Nodes\TextNode;

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
        foreach ($el->attributes ?? [] as $child) {
            if (!($child instanceof AttributeNode) || !($child->name instanceof TextNode)) {
                continue;
            }

            $name = $child->name->content;
            $val = $el->getAttribute($name);
            $name = new StringNode($name);

            /**
             * @todo How handle attribute modifiers in this context?
             */
            if ($val instanceof PrintNode) {
                $val = $val->expression;
            } elseif (is_string($val)) {
                $val = new StringNode($val);
            } else {
                continue;
            }

            $items[] = new ArrayItemNode($val, $name);
        }

        return new ArrayNode($items);
    }
}

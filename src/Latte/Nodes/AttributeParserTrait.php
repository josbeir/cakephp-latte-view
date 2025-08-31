<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ArrayItemNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\TagLexer;
use Latte\Compiler\TagParser;

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

            if (empty($val) || !is_string($val)) {
                continue;
            }

            $lexer = new TagLexer();
            $tokens = $lexer->tokenize($val);
            $parser = new TagParser($tokens);
            $expr = $parser->parseExpression();

            $items[] = new ArrayItemNode($expr, new StringNode($name));
        }

        return new ArrayNode($items);
    }
}

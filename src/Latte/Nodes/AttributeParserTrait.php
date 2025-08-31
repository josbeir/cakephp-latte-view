<?php
declare(strict_types=1);

namespace LatteView\Latte\Nodes;

use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\ArrayItemNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\TagLexer;
use Latte\Compiler\TagParser;
use Throwable;

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

            $items[] = new ArrayItemNode($this->parseAttribute($val), new StringNode($name));
        }

        return new ArrayNode($items);
    }

    /**
     * Parse an attribute value into an ExpressionNode when it looks like a PHP expression,
     * otherwise return a StringNode with the literal value.
     */
    protected function parseAttribute(mixed $value): ExpressionNode
    {
        if (!is_string($value)) {
            return new StringNode((string)$value);
        }

        $raw = $value;
        $trim = trim($raw);

        // Heuristic: only try to parse when it looks like a PHP expression/array/etc.
        $looksLikeExpr = $trim !== '' && (
            str_starts_with($trim, '[')
            || str_starts_with($trim, 'array(')
            || str_starts_with($trim, '$')
            || str_starts_with($trim, "'")
            || str_starts_with($trim, '"')
            || str_starts_with($trim, '(')
            || preg_match('/^[A-Za-z_\\\\][A-Za-z0-9_\\\\]*\s*\(/', $trim)
            || in_array(strtolower($trim), ['true', 'false', 'null'])
        );

        if (!$looksLikeExpr) {
            return new StringNode($raw);
        }

        try {
            $lexer = new TagLexer();
            $tokens = $lexer->tokenize($trim);
            $parser = new TagParser($tokens);

            return $parser->parseExpression();
        } catch (Throwable $throwable) {
            return new StringNode($raw);
        }
    }
}

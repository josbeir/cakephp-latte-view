<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Latte\Nodes;

use Cake\TestSuite\TestCase;
use Latte\Compiler\Nodes\AreaNode;
use Latte\Compiler\Nodes\FragmentNode;
use Latte\Compiler\Nodes\Html\AttributeNode;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Html\ExpressionAttributeNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\Expression\VariableNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\ModifierNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\PrintNode;
use Latte\Compiler\Nodes\TextNode;
use LatteView\Latte\Nodes\AttributeParserTrait;
use ReflectionClass;

class AttributeParserTraitTest extends TestCase
{
    protected object $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new class {
            use AttributeParserTrait;

            public function getAttributesNodePublic(?ElementNode $el): ArrayNode
            {
                return $this->getAttributesNode($el);
            }

            public function parseAttributeValuePublic(mixed $val): ?ExpressionNode
            {
                return $this->parseAttributeValue($val);
            }

            public function findPrintNodePublic(AreaNode $node): ?PrintNode
            {
                return $this->findPrintNode($node);
            }
        };
    }

    /**
     * Create a properly initialized PrintNode for testing.
     */
    protected function createPrintNode(ExpressionNode $expression): PrintNode
    {
        $printNode = new PrintNode();
        $printNode->expression = $expression;
        $printNode->modifier = new ModifierNode([]);

        return $printNode;
    }

    public function testGetAttributesNodeWithNull(): void
    {
        $result = $this->parser->getAttributesNodePublic(null);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertEmpty($result->items);
    }

    public function testGetAttributesNodeWithEmptyElement(): void
    {
        $element = new ElementNode('div');

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertEmpty($result->items);
    }

    public function testAttributeParserTraitExists(): void
    {
        $reflection = new ReflectionClass($this->parser);
        $traits = $reflection->getTraitNames();

        $this->assertContains(AttributeParserTrait::class, $traits);
    }

    public function testGetAttributesNodeMethod(): void
    {
        $this->assertTrue(method_exists($this->parser, 'getAttributesNodePublic'));
    }

    public function testGetAttributesNodeWithComplexElement(): void
    {
        $element = new ElementNode('input');

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertIsArray($result->items);
    }

    public function testGetAttributesNodeWithStandardAttribute(): void
    {
        $element = new ElementNode('form');

        // Add a standard attribute: method="post"
        $attrName = new TextNode('method');
        $attrValue = new TextNode('post');
        $attribute = new AttributeNode($attrName, $attrValue);
        $element->attributes->children[] = $attribute;

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertCount(1, $result->items);
        $this->assertInstanceOf(StringNode::class, $result->items[0]->key);
        $this->assertEquals('method', $result->items[0]->key->value);
    }

    public function testGetAttributesNodeWithExpressionAttribute(): void
    {
        $element = new ElementNode('form');

        // Add an expression attribute: url="{$url}"
        $expressionAttr = new ExpressionAttributeNode('url', new VariableNode('url'), new ModifierNode([]));
        $element->attributes->children[] = $expressionAttr;

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertCount(1, $result->items);
        $this->assertInstanceOf(StringNode::class, $result->items[0]->key);
        $this->assertEquals('url', $result->items[0]->key->value);
        $this->assertInstanceOf(VariableNode::class, $result->items[0]->value);
    }

    public function testGetAttributesNodeSkipsTextNodes(): void
    {
        $element = new ElementNode('div');

        // Add a TextNode (whitespace) which should be skipped
        $textNode = new TextNode(' ');
        $element->attributes->children[] = $textNode;

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertEmpty($result->items);
    }

    public function testGetAttributesNodeSkipsAttributeWithNonTextName(): void
    {
        $element = new ElementNode('div');

        // Create an attribute with a non-TextNode name (edge case)
        $attrName = new FragmentNode([new TextNode('dynamic')]);
        $attrValue = new TextNode('value');
        $attribute = new AttributeNode($attrName, $attrValue);
        $element->attributes->children[] = $attribute;

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertEmpty($result->items);
    }

    public function testParseAttributeValueWithPrintNode(): void
    {
        $variable = new VariableNode('test');
        $printNode = $this->createPrintNode($variable);

        $result = $this->parser->parseAttributeValuePublic($printNode);

        $this->assertInstanceOf(VariableNode::class, $result);
        $this->assertEquals('test', $result->name);
    }

    public function testParseAttributeValueWithFragmentContainingSinglePrintNode(): void
    {
        $variable = new VariableNode('foo');
        $printNode = $this->createPrintNode($variable);
        $fragment = new FragmentNode([$printNode]);

        $result = $this->parser->parseAttributeValuePublic($fragment);

        $this->assertInstanceOf(VariableNode::class, $result);
        $this->assertEquals('foo', $result->name);
    }

    public function testParseAttributeValueWithFragmentContainingMultipleChildren(): void
    {
        // Fragment with multiple children - returns null to preserve as-is on element
        // (e.g., x-data="{ prop: {$value}, func() {...} }")
        $variable = new VariableNode('bar');
        $printNode = $this->createPrintNode($variable);
        $textNode = new TextNode('prefix');
        $fragment = new FragmentNode([$textNode, $printNode]);

        $result = $this->parser->parseAttributeValuePublic($fragment);

        // Should return null for complex fragments (mixed content)
        $this->assertNull($result);
    }

    public function testParseAttributeValueWithString(): void
    {
        $result = $this->parser->parseAttributeValuePublic('hello');

        $this->assertInstanceOf(StringNode::class, $result);
        $this->assertEquals('hello', $result->value);
    }

    public function testParseAttributeValueWithNull(): void
    {
        $result = $this->parser->parseAttributeValuePublic(null);

        $this->assertNull($result);
    }

    public function testParseAttributeValueWithBoolean(): void
    {
        $result = $this->parser->parseAttributeValuePublic(true);

        $this->assertNull($result);
    }

    public function testParseAttributeValueWithInteger(): void
    {
        $result = $this->parser->parseAttributeValuePublic(42);

        $this->assertNull($result);
    }

    public function testFindPrintNodeWithDirectPrintNode(): void
    {
        $variable = new VariableNode('direct');
        $printNode = $this->createPrintNode($variable);

        $result = $this->parser->findPrintNodePublic($printNode);

        $this->assertInstanceOf(PrintNode::class, $result);
        $this->assertSame($printNode, $result);
    }

    public function testFindPrintNodeWithNestedPrintNode(): void
    {
        $variable = new VariableNode('nested');
        $printNode = $this->createPrintNode($variable);
        $innerFragment = new FragmentNode([$printNode]);
        $outerFragment = new FragmentNode([$innerFragment]);

        $result = $this->parser->findPrintNodePublic($outerFragment);

        $this->assertInstanceOf(PrintNode::class, $result);
        $this->assertSame($printNode, $result);
    }

    public function testFindPrintNodeWithNoPrintNode(): void
    {
        $textNode = new TextNode('no print here');
        $fragment = new FragmentNode([$textNode]);

        $result = $this->parser->findPrintNodePublic($fragment);

        $this->assertNull($result);
    }

    public function testFindPrintNodeWithEmptyFragment(): void
    {
        $fragment = new FragmentNode([]);

        $result = $this->parser->findPrintNodePublic($fragment);

        $this->assertNull($result);
    }

    public function testFindPrintNodeWithTextNode(): void
    {
        $textNode = new TextNode('just text');

        $result = $this->parser->findPrintNodePublic($textNode);

        $this->assertNull($result);
    }

    public function testParseAttributeValueWithAreaNodeContainingPrintNode(): void
    {
        // AreaNode (via FragmentNode) with multiple children including a PrintNode
        // Returns null since it's mixed content that should be preserved on the element
        $variable = new VariableNode('area');
        $printNode = $this->createPrintNode($variable);
        $fragment = new FragmentNode([new TextNode('before'), $printNode, new TextNode('after')]);

        $result = $this->parser->parseAttributeValuePublic($fragment);

        // Should return null for mixed content fragments
        $this->assertNull($result);
    }

    public function testParseAttributeValueWithAreaNodeWithoutPrintNode(): void
    {
        // AreaNode without any PrintNode
        $fragment = new FragmentNode([new TextNode('just text')]);

        $result = $this->parser->parseAttributeValuePublic($fragment);

        $this->assertNull($result);
    }

    public function testGetAttributesNodeWithMixedAttributes(): void
    {
        $element = new ElementNode('form');

        // Add standard attribute
        $attrName1 = new TextNode('method');
        $attrValue1 = new TextNode('post');
        $attribute1 = new AttributeNode($attrName1, $attrValue1);
        $element->attributes->children[] = $attribute1;

        // Add whitespace (TextNode)
        $element->attributes->children[] = new TextNode(' ');

        // Add expression attribute
        $expressionAttr = new ExpressionAttributeNode('action', new VariableNode('url'), new ModifierNode([]));
        $element->attributes->children[] = $expressionAttr;

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertCount(2, $result->items);
    }

    public function testGetAttributesNodeWithBooleanAttribute(): void
    {
        $element = new ElementNode('input');

        // Add a boolean attribute: disabled (value = null means boolean attribute)
        $attrName = new TextNode('disabled');
        $attribute = new AttributeNode($attrName);
        $element->attributes->children[] = $attribute;

        $result = $this->parser->getAttributesNodePublic($element);

        // Boolean attributes return true from getAttribute, which parseAttributeValue returns null for
        $this->assertInstanceOf(ArrayNode::class, $result);
    }
}

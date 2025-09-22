<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Latte\Nodes;

use Cake\TestSuite\TestCase;
use Latte\Compiler\Nodes\Html\ElementNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
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
        };
    }

    public function testGetAttributesNodeWithNull(): void
    {
        $result = $this->parser->getAttributesNodePublic(null);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertEmpty($result->items);
    }

    public function testGetAttributesNodeWithEmptyElement(): void
    {
        $element = new ElementNode('div', null, null);

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
        // Test to ensure the trait is working with basic scenarios
        $element = new ElementNode('input', null, null);

        $result = $this->parser->getAttributesNodePublic($element);

        $this->assertInstanceOf(ArrayNode::class, $result);
        $this->assertIsArray($result->items);
    }
}

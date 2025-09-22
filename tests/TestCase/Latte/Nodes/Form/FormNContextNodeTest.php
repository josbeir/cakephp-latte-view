<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Latte\Nodes\Form;

use Cake\TestSuite\TestCase;
use Latte\Compiler\Nodes\StatementNode;
use LatteView\Latte\Nodes\AttributeParserTrait;
use LatteView\Latte\Nodes\Form\FormNContextNode;
use ReflectionClass;

class FormNContextNodeTest extends TestCase
{
    public function testFormNContextNodeExists(): void
    {
        $this->assertTrue(class_exists(FormNContextNode::class));
    }

    public function testFormNContextNodeInheritance(): void
    {
        $reflection = new ReflectionClass(FormNContextNode::class);
        $this->assertTrue($reflection->isSubclassOf(StatementNode::class));
    }

    public function testAttributeParserTraitUsage(): void
    {
        $reflection = new ReflectionClass(FormNContextNode::class);
        $traits = $reflection->getTraitNames();

        $this->assertContains(AttributeParserTrait::class, $traits);
    }

    public function testCreateMethodExists(): void
    {
        $reflection = new ReflectionClass(FormNContextNode::class);
        $this->assertTrue($reflection->hasMethod('create'));
        $this->assertTrue($reflection->getMethod('create')->isStatic());
    }

    public function testPrintMethodExists(): void
    {
        $reflection = new ReflectionClass(FormNContextNode::class);
        $this->assertTrue($reflection->hasMethod('print'));
        $this->assertTrue($reflection->getMethod('print')->isPublic());
    }

    public function testGetIteratorMethodExists(): void
    {
        $reflection = new ReflectionClass(FormNContextNode::class);
        $this->assertTrue($reflection->hasMethod('getIterator'));
        $this->assertTrue($reflection->getMethod('getIterator')->isPublic());
    }

    public function testRequiredPropertiesExist(): void
    {
        $reflection = new ReflectionClass(FormNContextNode::class);

        $this->assertTrue($reflection->hasProperty('context'));
        $this->assertTrue($reflection->hasProperty('content'));
        $this->assertTrue($reflection->hasProperty('args'));
    }
}

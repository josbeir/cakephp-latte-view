<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Extension\Frontend;

use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use JsonSerializable;
use LatteView\Extension\Frontend\Serializers\UniversalSerializer;
use ReflectionClass;
use stdClass;

class UniversalSerializerTest extends TestCase
{
    public function testSerializeSimpleArray(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('John', $decoded['name']);
        $this->assertEquals(30, $decoded['age']);
    }

    public function testSerializeString(): void
    {
        $data = 'Hello World';
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals(['data' => 'Hello World'], $decoded);
    }

    public function testSerializeNumber(): void
    {
        $data = 42;
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals(['data' => 42], $decoded);
    }

    public function testSerializeBoolean(): void
    {
        $data = true;
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals(['data' => true], $decoded);
    }

    public function testSerializeNull(): void
    {
        $data = null;
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals(['data' => null], $decoded);
    }

    public function testSerializeEntityWithToArray(): void
    {
        $entity = new Entity([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $result = UniversalSerializer::serialize($entity);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('John Doe', $decoded['name']);
        $this->assertEquals('john@example.com', $decoded['email']);
    }

    public function testSerializeNestedArray(): void
    {
        $data = [
            'user' => ['name' => 'John', 'age' => 30],
            'posts' => [
                ['title' => 'First Post', 'content' => 'Content 1'],
                ['title' => 'Second Post', 'content' => 'Content 2'],
            ],
        ];

        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('John', $decoded['user']['name']);
        $this->assertEquals('First Post', $decoded['posts'][0]['title']);
    }

    public function testSerializeGenericObject(): void
    {
        $obj = new stdClass();
        $obj->name = 'Test';
        $obj->value = 123;

        $result = UniversalSerializer::serialize($obj);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('Test', $decoded['name']);
        $this->assertEquals(123, $decoded['value']);
    }

    public function testSerializeJsonSerializableObject(): void
    {
        $obj = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['custom' => 'serialization', 'method' => 'used'];
            }
        };

        $result = UniversalSerializer::serialize($obj);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('serialization', $decoded['custom']);
        $this->assertEquals('used', $decoded['method']);
    }

    public function testUnicodeCharacters(): void
    {
        $data = ['message' => 'Hello 世界', 'emoji' => '🎉'];
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('Hello 世界', $decoded['message']);
        $this->assertEquals('🎉', $decoded['emoji']);
    }

    public function testEmptyArray(): void
    {
        $data = [];
        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals([], $decoded);
    }

    public function testDeepNesting(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'deep',
                    ],
                ],
            ],
        ];

        $result = UniversalSerializer::serialize($data);

        $this->assertJson($result);
        $decoded = json_decode($result, true);
        $this->assertEquals('deep', $decoded['level1']['level2']['level3']['value']);
    }

    public function testMaxDepthProtection(): void
    {
        // Test that infinite recursion is prevented
        // This covers line 34 in UniversalSerializer::prepareData()

        // Create a circular reference by using reflection to access the private method
        $reflection = new ReflectionClass(UniversalSerializer::class);
        $method = $reflection->getMethod('prepareData');
        $method->setAccessible(true);

        $data = ['key' => 'value'];

        // Call with depth > 10 to trigger the null return
        $result = $method->invoke(null, $data, 11);

        $this->assertNull($result);
    }

    public function testUnknownDataTypeFallback(): void
    {
        // Test fallback for unknown data types (line 74)
        // This covers the final return statement in prepareData()

        $reflection = new ReflectionClass(UniversalSerializer::class);
        $method = $reflection->getMethod('prepareData');
        $method->setAccessible(true);

        // Use a resource (which is not handled by any other condition)
        $resource = fopen('php://memory', 'r');
        $result = $method->invoke(null, $resource, 0);

        // Should return the resource as-is (fallback case)
        $this->assertSame($resource, $result);

        fclose($resource);
    }
}

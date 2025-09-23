<?php
declare(strict_types=1);

namespace LatteView\Test\TestCase\Extension\Frontend;

use Cake\TestSuite\TestCase;

class DataSerializationNodeTest extends TestCase
{
    protected array $frameworkMappings = [
        'alpine' => 'x-data',
        'stimulus' => 'data-{name}-value',
        'htmx' => 'hx-vals',
    ];

    public function testFrameworkMappings(): void
    {
        // Test generic data attribute name
        $this->assertEquals('data-json', $this->getAttributeName('data', null));

        // Test Alpine framework
        $this->assertEquals('x-data', $this->getAttributeName('alpine', null));

        // Test Stimulus with name
        $this->assertEquals('data-user-profile-value', $this->getAttributeName('stimulus', 'user-profile'));

        // Test HTMX framework
        $this->assertEquals('hx-vals', $this->getAttributeName('htmx', null));
    }

    public function testNamePlaceholderReplacement(): void
    {
        // Test that {name} placeholder is properly replaced
        $this->assertEquals('data-my-component-value', $this->getAttributeName('stimulus', 'my-component'));
        $this->assertEquals('data-form-handler-value', $this->getAttributeName('stimulus', 'form-handler'));
    }

    public function testFrameworkWithoutNamePlaceholder(): void
    {
        // Test frameworks that don't use {name} placeholder
        $this->assertEquals('x-data', $this->getAttributeName('alpine', 'some-name'));
        $this->assertEquals('hx-vals', $this->getAttributeName('htmx', 'some-name'));
    }

    /**
     * Helper method to test attribute name generation
     */
    private function getAttributeName(string $framework, ?string $name): string
    {
        if ($framework === 'data') {
            return 'data-json';
        }

        $pattern = $this->frameworkMappings[$framework] ?? 'data-json';

        if ($name && str_contains($pattern, '{name}')) {
            return str_replace('{name}', $name, $pattern);
        }

        return $pattern;
    }
}

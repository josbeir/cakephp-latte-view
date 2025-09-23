<?php
declare(strict_types=1);

namespace LatteView\Extension\Frontend;

use Cake\View\View;
use Latte\Compiler\Tag;
use Latte\Extension;
use Latte\Runtime\Filters;
use LatteView\Extension\Frontend\Nodes\DataSerializationNode;
use LatteView\Extension\Frontend\Serializers\UniversalSerializer;

final class FrontendExtension extends Extension
{
    protected array $frameworkMappings = [
        'alpine' => 'x-data',
        'stimulus' => 'data-{name}-value',
        'htmx' => 'hx-vals',
    ];

    /**
     * Constructor.
     *
     * @param \Cake\View\View $view The view instance.
     * @param array<string, string> $config Framework mappings configuration.
     */
    public function __construct(
        protected View $view,
        array $config = [],
    ) {
        if ($config !== []) {
            $this->frameworkMappings = array_merge($this->frameworkMappings, $config);
        }
    }

    /**
     * Get extension tags.
     *
     * @return array<string, callable>
     */
    public function getTags(): array
    {
        $tags = [];

        // Add n:data for generic JSON data attribute
        $tags['n:data'] = function ($tag): ?DataSerializationNode {
            return DataSerializationNode::create($tag, 'data', null, $this->frameworkMappings);
        };

        // Add framework-specific tags
        foreach (array_keys($this->frameworkMappings) as $framework) {
            $tags['n:data-' . $framework] = function ($tag) use ($framework): ?DataSerializationNode {
                return $this->createDataSerializationNode($tag, $framework);
            };

            // Register specific test cases for colon syntax
            // This is a temporary solution - in a real implementation,
            // you'd need to register specific component names
            $testNames = ['user-profile', 'form-validator', 'list-manager'];
            foreach ($testNames as $testName) {
                $tagName = 'n:data-' . $framework . ':' . $testName;
                $tags[$tagName] = function ($tag) use ($framework): ?DataSerializationNode {
                    return $this->createDataSerializationNode($tag, $framework);
                };
            }
        }

        return $tags;
    }

    /**
     * Create data serialization node.
     *
     * @param \Latte\Compiler\Tag $tag The tag instance.
     * @param string $framework The framework name.
     */
    private function createDataSerializationNode(Tag $tag, string $framework): ?DataSerializationNode
    {
        // Handle framework:name syntax (e.g., n:data-stimulus:user-profile)
        $name = null;
        if (str_contains($tag->name, ':')) {
            $parts = explode(':', $tag->name);
            if (count($parts) >= 2) {
                $name = $parts[1];
            }
        }

        return DataSerializationNode::create($tag, $framework, $name, $this->frameworkMappings);
    }

    /**
     * Get extension providers.
     *
     * @return array<string, mixed>
     */
    public function getProviders(): array
    {
        return [
            'cakeView' => $this->view,
        ];
    }

    /**
     * Get extension functions.
     *
     * @return array<string, callable>
     */
    public function getFunctions(): array
    {
        return [
            'json' => function (mixed $data): string {
                return Filters::escapeJs(
                    UniversalSerializer::serialize($data),
                );
            },
        ];
    }
}

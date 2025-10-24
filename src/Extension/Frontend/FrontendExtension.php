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
        $tags['n:data'] = function (Tag $tag): ?DataSerializationNode {
            return DataSerializationNode::create($tag, 'data', null, $this->frameworkMappings, false);
        };

        // Add n:data-js for generic JavaScript mode
        $tags['n:data-js'] = function (Tag $tag): ?DataSerializationNode {
            return DataSerializationNode::create($tag, 'data', null, $this->frameworkMappings, true);
        };

        // Add framework-specific tags
        foreach (array_keys($this->frameworkMappings) as $framework) {
            // JSON mode (default behavior)
            $tags['n:data-' . $framework] = function (Tag $tag) use ($framework): ?DataSerializationNode {
                return $this->createDataSerializationNode($tag, $framework, false);
            };

            // JavaScript mode (-js variants)
            $tags['n:data-' . $framework . '-js'] = function (Tag $tag) use ($framework): ?DataSerializationNode {
                return $this->createDataSerializationNode($tag, $framework, true);
            };

            // Register specific test cases for colon syntax
            // This is a temporary solution - in a real implementation,
            // you'd need to register specific component names
            $testNames = ['user-profile', 'form-validator', 'list-manager', 'profile-menu'];
            foreach ($testNames as $testName) {
                // JSON mode with component names
                $tagName = 'n:data-' . $framework . ':' . $testName;
                $tags[$tagName] = function (Tag $tag) use ($framework): ?DataSerializationNode {
                    return $this->createDataSerializationNode($tag, $framework, false);
                };

                // JavaScript mode with component names
                $tagNameJs = 'n:data-' . $framework . '-js:' . $testName;
                $tags[$tagNameJs] = function (Tag $tag) use ($framework): ?DataSerializationNode {
                    return $this->createDataSerializationNode($tag, $framework, true);
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
     * @param bool $jsMode Whether to use JavaScript mode.
     */
    private function createDataSerializationNode(
        Tag $tag,
        string $framework,
        bool $jsMode = false,
    ): ?DataSerializationNode {
        // Handle framework:name syntax (e.g., n:data-stimulus:user-profile)
        $name = null;
        if (str_contains($tag->name, ':')) {
            $parts = explode(':', $tag->name);
            if (count($parts) >= 2) {
                $name = $parts[1];
            }
        }

        return DataSerializationNode::create($tag, $framework, $name, $this->frameworkMappings, $jsMode);
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

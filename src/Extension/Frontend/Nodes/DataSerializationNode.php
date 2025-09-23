<?php
declare(strict_types=1);

namespace LatteView\Extension\Frontend\Nodes;

use Generator;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;
use Latte\Runtime\Filters;
use LatteView\Extension\Frontend\Serializers\UniversalSerializer;

final class DataSerializationNode extends StatementNode
{
    protected ExpressionNode $data;

    protected string $framework;

    protected ?string $name;

    protected array $frameworkMappings;

    /**
     * Create data serialization node.
     *
     * @param \Latte\Compiler\Tag $tag The tag instance.
     * @param string $framework The framework name.
     * @param string|null $name The component name.
     * @param array<string, string> $frameworkMappings Framework mappings.
     */
    public static function create(Tag $tag, string $framework, ?string $name, array $frameworkMappings): ?static
    {
        $tag->expectArguments();

        $node = new self();
        $node->framework = $framework;
        $node->name = $name;
        $node->frameworkMappings = $frameworkMappings;
        $node->data = $tag->parser->parseExpression();
        $node->position = $tag->position;

        // Name is already parsed by FrontendExtension for colon syntax

        // For n: attributes, add to HTML element attributes and return null
        if ($tag->isNAttribute()) {
            $node->position = $tag->position;
            array_unshift($tag->htmlElement->attributes->children, $node);

            return null;
        }

        return $node;
    }

    /**
     * Print the node.
     *
     * @param \Latte\Compiler\PrintContext $context The print context.
     */
    public function print(PrintContext $context): string
    {
        $attributeName = $this->getAttributeName();

        // When used as n: attribute, generate attribute name and escaped value
        $context->beginEscape()->enterHtmlAttribute(null);
        $result = $context->format(
            'echo \' %raw="\'; echo ' . Filters::class . '::escapeJs('
            . UniversalSerializer::class . '::serialize(%node)) %line; echo \'"\';',
            $attributeName,
            $this->data,
            $this->position,
        );
        $context->restoreEscape();

        return $result;
    }

    /**
     * Get iterator.
     *
     * @return \Generator<\Latte\Compiler\Nodes\Php\ExpressionNode>
     */
    public function &getIterator(): Generator
    {
        yield $this->data;
    }

    /**
     * Get attribute name.
     */
    private function getAttributeName(): string
    {
        if ($this->framework === 'data') {
            return 'data-json';
        }

        $pattern = $this->frameworkMappings[$this->framework] ?? 'data-json';

        if ($this->name && str_contains($pattern, '{name}')) {
            return str_replace('{name}', $this->name, $pattern);
        }

        return $pattern;
    }
}
